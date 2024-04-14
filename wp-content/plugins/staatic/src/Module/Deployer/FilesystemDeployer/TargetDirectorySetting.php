<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\FilesystemDeployer;

use Staatic\WordPress\Service\Filesystem;
use Staatic\WordPress\Setting\AbstractSetting;

final class TargetDirectorySetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_filesystem_target_directory';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    public function label() : string
    {
        return \__('Target Directory', 'staatic');
    }

    public function description() : ?string
    {
        return \__('The path to the directory on the filesystem where the static version of your site is deployed.', 'staatic');
    }

    public function sanitizeValue($value)
    {
        $path = \untrailingslashit(Filesystem::normalizePath($value));
        if (strncmp(Filesystem::getRootPath(), $path, strlen($path)) === 0) {
            \add_settings_error('staatic-settings', 'invalid_filesystem_target_directory', \sprintf(
                /* translators: %s: Supplied target directory. */
                \__('The supplied target directory "%s" is on the same level (or higher) as to where WordPress itself is installed. This would overwrite your WordPress installation and possibly more. Please choose another directory to publish your site to.', 'staatic'),
                \esc_html($value)
            ));

            return $this->defaultValue();
        }

        return $path;
    }

    public function defaultValue()
    {
        return Filesystem::getUploadsPath() . 'staatic/deploy/';
    }
}
