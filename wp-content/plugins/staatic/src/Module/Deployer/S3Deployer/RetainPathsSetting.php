<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Deployer\S3Deployer;

use Staatic\WordPress\Setting\AbstractSetting;

final class RetainPathsSetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_aws_retain_paths';
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
            \__('Optionally add file or directory paths (absolute or relative to the bucket prefix) that need to be left intact (one path per line).<br>Files existing in the target bucket that are not part of the build and not in this list will be deleted during deployment.<br>Examples: %s.', 'staatic'),
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
        $retainPaths = [];
        foreach (\explode("\n", $value) as $retainPath) {
            $retainPath = \trim($retainPath);
            // Retain empty or commented lines
            if (!$retainPath || strncmp($retainPath, '#', strlen('#')) === 0) {
                $retainPaths[] = $retainPath;

                continue;
            }
            if (!\in_array($retainPath, $retainPaths)) {
                $retainPaths[] = $retainPath;
            }
        }

        return \implode("\n", $retainPaths);
    }

    /**
     * @param string|null $value
     * @param string|null $bucketPrefix
     */
    public static function resolvedValue($value, $bucketPrefix) : array
    {
        $resolvedValue = [];
        if ($value === null) {
            return $resolvedValue;
        }
        foreach (\explode("\n", $value) as $retainPath) {
            if (!$retainPath || strncmp($retainPath, '#', strlen('#')) === 0) {
                continue;
            }
            $resolvedValue[] = strncmp($retainPath, '/', strlen('/')) === 0 ? \substr(
                $retainPath,
                1
            ) : ($bucketPrefix ? \sprintf(
                '%s/%s',
                \untrailingslashit($bucketPrefix),
                $retainPath
            ) : $retainPath);
        }

        return $resolvedValue;
    }
}
