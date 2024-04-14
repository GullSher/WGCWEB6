<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\FilesystemDeployer;

use Staatic\WordPress\Setting\AbstractSetting;

final class RetainPathsSetting extends AbstractSetting
{
    /**
     * @var TargetDirectorySetting
     */
    private $targetDirectory;

    public function __construct(TargetDirectorySetting $targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function name() : string
    {
        return 'staatic_filesystem_retain_paths';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    protected function template() : string
    {
        return 'retain_paths';
    }

    public function label() : string
    {
        return \__('Retain Files/Directories', 'staatic');
    }

    public function description() : ?string
    {
        return \sprintf(
            /* translators: %s: Example paths. */
            \__('Optionally add file or directory paths (absolute or relative to the target directory) that need to be left intact (one path per line).<br>Files existing in the target directory that are not part of the build and not in this list will be deleted during deployment.<br>Examples: %s.', 'staatic'),
            \implode(
                ', ',
                ['<code>favicon.ico</code>',
                '<code>robots.txt</code>',
                \__('a Bing/Google/Yahoo/etc. verification file', 'staatic')
            ])
        );
    }

    public function sanitizeValue($value)
    {
        $targetDirectory = \rtrim($this->targetDirectory->value(), '\\/');
        $retainPaths = [];
        foreach (\explode("\n", $value) as $retainPath) {
            $retainPath = \trim($retainPath);
            // Retain empty or commented lines
            if (!$retainPath || strncmp($retainPath, '#', strlen('#')) === 0) {
                $retainPaths[] = $retainPath;

                continue;
            }
            $absolutePath = strncmp($retainPath, '/', strlen('/')) === 0 ? $retainPath : \sprintf(
                '%s/%s',
                $targetDirectory,
                $retainPath
            );
            if (!\file_exists($absolutePath)) {
                \add_settings_error('staatic-settings', 'invalid_retain_path', \sprintf(
                    /* translators: %s: Supplied retain path. */
                    \__('The supplied retain path "%s" does not exist', 'staatic'),
                    \esc_html($absolutePath)
                ), 'warning');
            }
            if (!\in_array($retainPath, $retainPaths)) {
                $retainPaths[] = $retainPath;
            }
        }

        return \implode("\n", $retainPaths);
    }

    /**
     * @param string|null $value
     * @param string $basePath
     */
    public static function resolvedValue($value, $basePath) : array
    {
        $resolvedValue = [];
        if ($value === null) {
            return $resolvedValue;
        }
        foreach (\explode("\n", $value) as $retainPath) {
            if (!$retainPath || strncmp($retainPath, '#', strlen('#')) === 0) {
                continue;
            }
            $resolvedValue[] = strncmp($retainPath, '/', strlen('/')) === 0 ? $retainPath : \sprintf(
                '%s/%s',
                \untrailingslashit($basePath),
                $retainPath
            );
        }

        return $resolvedValue;
    }
}
