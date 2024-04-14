<?php

declare(strict_types=1);

namespace Staatic\WordPress\Publication\Task;

use Staatic\Vendor\Psr\Log\LoggerInterface;
use RuntimeException;
use Staatic\Framework\DeployStrategy\DeployStrategyInterface;
use Staatic\WordPress\Publication\Publication;
use Staatic\WordPress\Service\Filesystem;
use Staatic\WordPress\Setting\Advanced\WorkDirectorySetting;

final class SetupTask implements TaskInterface
{
    /**
     * @var \Staatic\WordPress\Setting\Advanced\WorkDirectorySetting
     */
    private $workDirectory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Staatic\WordPress\Service\Filesystem
     */
    private $filesystem;

    public function __construct(WorkDirectorySetting $workDirectory, LoggerInterface $logger, Filesystem $filesystem)
    {
        $this->workDirectory = $workDirectory;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
    }

    public function name() : string
    {
        return 'setup';
    }

    public function description() : string
    {
        return \__('Setting up', 'staatic');
    }

    /**
     * @param Publication $publication
     */
    public function supports($publication) : bool
    {
        return \true;
    }

    /**
     * @param Publication $publication
     * @param bool $limitedResources
     */
    public function execute($publication, $limitedResources) : bool
    {
        $workDirectory = \untrailingslashit($this->workDirectory->value());
        $this->logger->info("Ensuring work directory exists in {$workDirectory}");
        $this->filesystem->ensureDirectoryExists($workDirectory);
        $resourceDirectory = \untrailingslashit($this->workDirectory->value()) . '/resources';
        $this->logger->info("Ensuring resource directory exists in {$resourceDirectory}");
        $this->filesystem->ensureDirectoryExists($resourceDirectory);
        $this->validateDeploymentMethod($publication);

        return \true;
    }

    private function validateDeploymentMethod(Publication $publication) : void
    {
        if (!\get_option('staatic_deployment_method')) {
            $this->invalidDeploymentMethod(\__('No deployment method has been selected yet', 'staatic'));
        }
        $errors = \apply_filters('staatic_deployment_strategy_validate', [], $publication);
        if (\count($errors) !== 0) {
            $this->invalidDeploymentMethod(\implode(', ', $errors));
        }
        $deployStrategy = \apply_filters('staatic_deployment_strategy', null, $publication);
        // In case false is returned, this essentially disables deployment and assumes deployment
        // related tasks are inactive.
        if ($deployStrategy === \false) {
            return;
        }
        if (!$deployStrategy instanceof DeployStrategyInterface) {
            $this->invalidDeploymentMethod(
                \__('Deployment method did not register "staatic_deployment_strategy" hook', 'staatic')
            );
        }
        if (\method_exists($deployStrategy, 'testConfiguration')) {
            $deployStrategy->testConfiguration();
        }
    }

    private function invalidDeploymentMethod(string $message) : void
    {
        throw new RuntimeException(\sprintf(
            /* translators: %s: Error message. */
            \__('Deployment has not been configured correctly: %s', 'staatic'),
            $message
        ));
    }
}
