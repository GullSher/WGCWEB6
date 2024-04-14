<?php

declare(strict_types=1);

namespace Staatic\WordPress\Cli;

use Staatic\Vendor\Psr\Log\LoggerInterface as PsrLoggerInterface;
use RuntimeException;
use Staatic\WordPress\Logging\Contextable;
use Staatic\WordPress\Logging\LoggerInterface;
use Staatic\WordPress\Publication\Publication;
use Staatic\WordPress\Publication\PublicationManagerInterface;
use Staatic\WordPress\Publication\PublicationRepository;
use Staatic\WordPress\Publication\PublicationTaskProvider;
use Staatic\WordPress\Publication\Task\CrawlTask;
use Staatic\WordPress\Publication\Task\DeployTask;
use Staatic\WordPress\Service\Formatter;
use Staatic\WordPress\Util\TimeLimit;
use Throwable;
use WP_CLI;
use function WP_CLI\Utils\get_flag_value;
use function WP_CLI\Utils\make_progress_bar;

class PublishCommand
{
    /**
     * @var PsrLoggerInterface
     */
    protected $logger;

    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * @var PublicationRepository
     */
    protected $publicationRepository;

    /**
     * @var PublicationManagerInterface
     */
    protected $publicationManager;

    /**
     * @var PublicationTaskProvider
     */
    protected $taskProvider;

    /**
     * @var bool
     */
    private $preview;

    public function __construct(PsrLoggerInterface $logger, Formatter $formatter, PublicationRepository $publicationRepository, PublicationManagerInterface $publicationManager, PublicationTaskProvider $taskProvider)
    {
        $this->logger = $logger;
        $this->formatter = $formatter;
        $this->publicationRepository = $publicationRepository;
        $this->publicationManager = $publicationManager;
        $this->taskProvider = $taskProvider;
    }

    /**
     * Initiates background process to publish static site.
     *
     * ## OPTIONS
     *
     * [--[no-]preview]
     * : Whether or not to create a preview build, if supported by the deployment method.
     * ---
     * default: false
     *
     * [--[no-]force]
     * : Whether or not to force publishing, even if another publication is in progress.
     * ---
     * default: false
     *
     * [--[no-]verbose]
     * : Whether or not to output log entries during publication.
     * ---
     * default: false
     * ---
     *
     * ## EXAMPLES
     *
     *     wp staatic publish
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assoc_args) : void
    {
        $this->preview = get_flag_value($assoc_args, 'preview', \false);
        $verbose = get_flag_value($assoc_args, 'verbose', \false);
        $force = get_flag_value($assoc_args, 'force', \false);
        if ($verbose && $this->logger instanceof LoggerInterface) {
            $this->logger->enableConsoleLogger();
        }
        if ($this->publicationManager->isPublicationInProgress()) {
            if ($force) {
                $publication = $this->publicationRepository->find(\get_option('staatic_current_publication_id'));
                $this->publicationManager->cancelPublication($publication);
                \update_option('staatic_current_publication_id', null);
            } else {
                WP_CLI::error(\__('Unable to publish; another publication is pending', 'staatic'));
            }
        }
        $publication = $this->createPublication();
        if ($this->publicationManager->claimPublication($publication)) {
            $this->startPublication($publication);
        } else {
            $this->publicationManager->cancelPublication($publication);

            throw new RuntimeException(\__('Unable to claim publication; another publication is pending', 'staatic'));
        }
    }

    protected function createPublication() : Publication
    {
        return $this->publicationManager->createPublication([], null, null, $this->preview);
    }

    /**
     * @param Publication $publication
     */
    protected function startPublication($publication) : void
    {
        $this->logger->notice(\__('Starting publication', 'staatic'), [
            'publicationId' => $publication->id()
        ]);
        if (!TimeLimit::setTimeLimit(0)) {
            $this->logger->warning('Unable to disable PHP time limit.');
        }
        $task = $this->taskProvider->firstTask();
        $publication->markInProgress();
        do {
            WP_CLI::line($task->description());
            $taskName = \get_class($task);
            if ($this->logger instanceof Contextable) {
                $this->logger->changeContext([
                    'publicationId' => $publication->id(),
                    'task' => $taskName
                ]);
            }
            $publication->setCurrentTask($taskName);
            $this->publicationRepository->update($publication);
            $this->logger->info($task->description());

            try {
                \do_action_deprecated('staatic_publication_before_task', [[
                    'publication' => $publication,
                    'task' => $task
                ]], '1.4.4', 'staatic_publication_task_before');
                \do_action('staatic_publication_task_before', $publication, $task);
            } catch (Throwable $failure) {
                $this->handleFailure($publication, $task->name(), $failure);
            }
            if ($taskName === CrawlTask::class) {
                $progress = make_progress_bar(\__('Crawling...', 'staatic'), 0);
                $ticks = 0;
            } elseif ($taskName === DeployTask::class) {
                $progress = make_progress_bar(\__('Deploying...', 'staatic'), 0);
                $ticks = 0;
            }
            do {
                try {
                    $taskFinished = $task->execute($publication, \true);
                    $this->updatePublicationUnlessCanceled($publication);
                } catch (Throwable $failure) {
                    $this->handleFailure($publication, $task->name(), $failure);
                }
                if ($taskName === CrawlTask::class) {
                    $addTicks = $publication->build()->numUrlsCrawled() - $ticks;
                    if ($addTicks) {
                        $progress->setTotal($publication->build()->numUrlsCrawlable());
                        $progress->tick($addTicks);
                        $ticks += $addTicks;
                    }
                } elseif ($taskName === DeployTask::class) {
                    $addTicks = $publication->deployment()->numResultsDeployed() - $ticks;
                    if ($addTicks) {
                        $progress->setTotal($publication->deployment()->numResultsDeployable());
                        $progress->tick($addTicks);
                        $ticks += $addTicks;
                    }
                }
                \gc_collect_cycles();
            } while (!$taskFinished);

            try {
                \do_action_deprecated('staatic_publication_after_task', [[
                    'publication' => $publication,
                    'task' => $task
                ]], '1.4.4', 'staatic_publication_task_after');
                \do_action('staatic_publication_task_after', $publication, $task);
            } catch (Throwable $failure) {
                $this->handleFailure($publication, $task->name(), $failure);
            }
            if ($taskName === CrawlTask::class || $taskName === DeployTask::class) {
                $progress->finish();
            }
        } while ($publication->status()->isInProgress() && ($task = $this->taskProvider->nextSupportedTask(
            $task,
            $publication
        )));
        $this->logger->notice(\__('Finished publication', 'staatic'), [
            'publicationId' => $publication->id()
        ]);
        WP_CLI::success(\sprintf(
            /* translators: %s: Date interval time taken. */
            \__('Publication finished in %s!', 'staatic'),
            $this->formatter->difference($publication->dateCreated(), $publication->dateFinished())
        ));
    }

    /**
     * @param Publication $publication
     * @param string $taskName
     * @param \Throwable $failure
     */
    protected function handleFailure($publication, $taskName, $failure) : void
    {
        \update_option('staatic_current_publication_id', '');
        $publication->markFailed();
        $this->publicationRepository->update($publication);
        $this->logger->critical(\sprintf(
            /* translators: 1: Publication task. */
            \__('Publication failed during %1$s task', 'staatic'),
            $taskName
        ), [
            'failure' => $failure
        ]);
        WP_CLI::error(\sprintf(
            /* translators: 1: Publication task, 2: Error type, 3: Error message. */
            \__('Publication failed during %1$s task with error %2$s: %3$s', 'staatic'),
            $taskName,
            \get_class($failure),
            $failure->getMessage()
        ));
    }

    /**
     * @param Publication $publication
     */
    protected function updatePublicationUnlessCanceled($publication) : void
    {
        if ($this->isPublicationCanceled($publication)) {
            $this->logger->warning(\__('Publication has been canceled', 'staatic'));
            WP_CLI::error(\__('Publication was canceled', 'staatic'));
        }
        $this->publicationRepository->update($publication);
    }

    /**
     * @param Publication $publication
     */
    protected function isPublicationCanceled($publication) : bool
    {
        $freshPublication = $this->publicationRepository->find($publication->id());

        return $freshPublication->status()->isCanceled();
    }
}
