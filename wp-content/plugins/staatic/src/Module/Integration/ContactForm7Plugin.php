<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Integration;

use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Service\Filesystem;
use WPCF7;

final class ContactForm7Plugin implements ModuleInterface
{
    public function hooks() : void
    {
        \add_action('wp_loaded', [$this, 'setupIntegration']);
    }

    public function setupIntegration() : void
    {
        if (!$this->isPluginActive()) {
            return;
        }
        \add_filter('staatic_additional_paths_exclude_paths', [$this, 'overrideAdditionalPathsExcludePaths']);
    }

    public function overrideAdditionalPathsExcludePaths(array $excludePaths) : array
    {
        // see: contact-form-7/includes/functions.php:62
        // contact-form-7/modules/really-simple-captcha.php:441
        $excludePaths[] = Filesystem::getUploadsPath() . 'wpcf7_captcha';
        $excludePaths[] = Filesystem::getUploadsPath() . 'wpcf7_uploads';

        return $excludePaths;
    }

    private function isPluginActive() : bool
    {
        return \class_exists(WPCF7::class);
    }
}
