<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module;

use Staatic\WordPress\Migrations\MigrationCoordinatorFactory;
use WP_Error;

class EnsureMigrated implements ModuleInterface
{
    /**
     * @var MigrationCoordinatorFactory
     */
    private $coordinatorFactory;

    /**
     * @var string
     */
    protected $namespace = 'staatic';

    public function __construct(MigrationCoordinatorFactory $coordinatorFactory)
    {
        $this->coordinatorFactory = $coordinatorFactory;
    }

    public function hooks() : void
    {
        if (!\is_admin()) {
            return;
        }
        \add_action('init', [$this, 'migrator'], 100);
    }

    public function migrator() : void
    {
        $coordinator = ($this->coordinatorFactory)($this->namespace);
        $retrying = ($_GET['staatic'] ?? null) === "_migrate_{$this->namespace}";
        if ($coordinator->hasMigrationFailed() && !$retrying) {
            $status = $coordinator->status();
            $this->handleMigrationError($status['error']['message'], $status['version'], $status['error']['version']);
        } elseif ($coordinator->isMigrating()) {
            \wp_die(new WP_Error('locked', \sprintf(
                /* translators: 1: Plugin Name. */
                \__('The %1$s database is being upgraded; please try again later.', 'staatic'),
                $this->pluginName()
            )));
        } elseif ($coordinator->shouldMigrate()) {
            $coordinator->migrate();
        }
        if ($retrying) {
            \wp_redirect(\admin_url());
            exit;
        }
    }

    /**
     * @param string $message
     * @param string $sourceVersion
     * @param string $targetVersion
     */
    protected function handleMigrationError($message, $sourceVersion, $targetVersion) : void
    {
        \add_action('admin_notices', function () use ($message, $sourceVersion, $targetVersion) {
            echo '<div class="error">';
            echo '<p><strong>' . \sprintf(
                /* translators: 1: Plugin Name. */
                \__('%1$s was unable to upgrade the database.', 'staatic'),
                $this->pluginName()
            ) . '</strong></p>';
            echo '<p>' . \sprintf(
                /* translators: 1: Link to Retry, 2: Link to Contact. */
                \__('You may retry the upgrade by <a href="%1$s">clicking here</a>. In case this does not resolve the issue, please <a href="%2$s" target="_blank" rel="noopener">contact Staatic support</a> while providing the following details:', 'staatic'),
                \admin_url("admin.php?staatic=_migrate_{$this->namespace}"),
                'staatic-publish_changes',
                'https://staatic.com/wordpress/contact/'
            ) . '</p>';
            echo '<dl>';
            echo '<dt>' . \esc_html__('Current version') . '</dt><dd>' . \esc_html($sourceVersion) . '</dd>';
            echo '<dt>' . \esc_html__('Target version') . '</dt><dd>' . \esc_html($targetVersion) . '</dd>';
            echo '<dt>' . \esc_html__('Error') . '</dt><dd>' . \esc_html($message) . '</dd>';
            echo '</dl>';
            echo '</div>';
        });
    }

    protected function pluginName() : string
    {
        return \__('Staatic', 'staatic');
    }

    public static function getDefaultPriority() : int
    {
        return \PHP_INT_MAX;
    }
}
