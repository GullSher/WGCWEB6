<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Admin\Page;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\WordPress\Module\Admin\Page\Publications\PublicationsPage;
use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Publication\Publication;
use Staatic\WordPress\Publication\PublicationManager;
use Staatic\WordPress\Publication\PublicationRepository;
use Staatic\WordPress\Service\AdminNavigation;
use Staatic\WordPress\Service\Filesystem;
use Staatic\WordPress\Service\PartialRenderer;
use Staatic\WordPress\Service\SiteUrlProvider;
use WP_Error;

final class PublishSubsetPage implements ModuleInterface
{
    /**
     * @var AdminNavigation
     */
    private $navigation;

    /**
     * @var PartialRenderer
     */
    private $renderer;

    /**
     * @var PublicationManager
     */
    private $publicationManager;

    /**
     * @var PublicationRepository
     */
    private $publicationRepository;

    /**
     * @var SiteUrlProvider
     */
    private $siteUrlProvider;

    /** @var string */
    public const PAGE_SLUG = 'staatic-publish-subset';

    use TriggersPublications;

    public function __construct(AdminNavigation $navigation, PartialRenderer $renderer, PublicationManager $publicationManager, PublicationRepository $publicationRepository, SiteUrlProvider $siteUrlProvider)
    {
        $this->navigation = $navigation;
        $this->renderer = $renderer;
        $this->publicationManager = $publicationManager;
        $this->publicationRepository = $publicationRepository;
        $this->siteUrlProvider = $siteUrlProvider;
    }

    public function hooks() : void
    {
        if (!\is_admin()) {
            return;
        }
        \add_action('init', [$this, 'addPage']);
    }

    public function addPage() : void
    {
        $this->navigation->addPage(
            \__('Publish Selection', 'staatic'),
            self::PAGE_SLUG,
            [$this, 'render'],
            'staatic_publish_subset',
            PublicationsPage::PAGE_SLUG
        );
    }

    public function render() : void
    {
        $errors = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            \check_admin_referer('staatic-publish-subset');
            $errors = $this->validate();
            if (!$errors->has_errors()) {
                $this->triggerPublication(\__('Publish Selection', 'staatic'), function () {
                    return $this->createPublication();
                });

                return;
            }
        }
        $urls = $_POST['urls'] ?? '';
        $paths = $_POST['paths'] ?? '';
        $deploy = isset($_POST['deploy']) ? (bool) $_POST['deploy'] : \true;
        $rootPath = Filesystem::getRootPath();
        $this->renderer->render('admin/publish-subset.php', \compact('urls', 'paths', 'deploy', 'errors', 'rootPath'));
    }

    private function validate() : WP_Error
    {
        $errors = new WP_Error();
        if (!\is_array($_POST)) {
            $errors->add('form', \__('<strong>Error</strong>: Missing form data.', 'staatic'));

            return $errors;
        }
        if (empty($_POST['urls']) && empty($_POST['paths'])) {
            $errors->add('form', \__('<strong>Error</strong>: Please add one or more URLs or paths.', 'staatic'));

            return $errors;
        }
        $siteUrl = ($this->siteUrlProvider)();
        foreach (\explode("\n", $_POST['urls']) as $url) {
            if (!($url = \trim($url))) {
                continue;
            }
            $authority = (new Uri($url))->getAuthority();
            if ($authority && $authority !== $siteUrl->getAuthority()) {
                $errors->add('urls', \sprintf(
                    /* translators: %s: Supplied url. */
                    \__('<strong>Error</strong>: URL "%s" is not part of this site.', 'staatic'),
                    \esc_html($url)
                ));
            }
        }
        $rootPath = Filesystem::getRootPath();
        foreach (\explode("\n", $_POST['paths']) as $path) {
            if (!($path = \trim($path))) {
                continue;
            }
            if (\realpath($path) === \false) {
                $errors->add('paths', \sprintf(
                    /* translators: %s: Supplied path. */
                    \__('<strong>Error</strong>: Path "%s" is not readable.', 'staatic'),
                    \esc_html($path)
                ));

                continue;
            }
            $normalizedPath = Filesystem::normalizePath($path);
            if (\preg_match('~^' . \preg_quote($rootPath, '~') . '~i', $normalizedPath) === 0) {
                $errors->add('paths', \sprintf(
                    /* translators: %s: Supplied path. */
                    \__('<strong>Error</strong>: Path "%s" is not web accessible.', 'staatic'),
                    \esc_html($path)
                ));

                continue;
            }
            if (\untrailingslashit($normalizedPath) === $rootPath) {
                $errors->add('paths', \sprintf(
                    /* translators: %s: Supplied path. */
                    \__('<strong>Error</strong>: Path "%s" is the WordPress installation directory and cannot be used.', 'staatic'),
                    \esc_html($path)
                ));

                continue;
            }
        }

        return $errors;
    }

    private function createPublication() : Publication
    {
        $args = [
            'subset' => [
                'urls' => \array_filter(\array_map(function ($value) {
                            return \trim($value);
                        }, \explode("\n", $_POST['urls']))),
                'paths' => \array_filter(\array_map(function ($value) {
                    return \trim($value);
                }, \explode("\n", $_POST['paths'])))
            ]
        ];
        if (empty($_POST['deploy'])) {
            $args['skipDeploy'] = \true;
        }

        return $this->publicationManager->createPublication($args);
    }
}
