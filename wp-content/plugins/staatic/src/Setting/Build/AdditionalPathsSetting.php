<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Build;

use Staatic\WordPress\Service\Filesystem;
use Staatic\WordPress\Setting\AbstractSetting;

final class AdditionalPathsSetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_additional_paths';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    public function label() : string
    {
        return \__('Additional Paths', 'staatic');
    }

    public function description() : ?string
    {
        return \sprintf(
            /* translators: %s: Example additional paths. */
            \__('Optionally add (filesystem) paths that need to be included in the build.<br>%s', 'staatic'),
            $this->examplesList([Filesystem::getUploadsPath()])
        );
    }

    public function defaultValue()
    {
        return Filesystem::getUploadsPath();
    }

    public function sanitizeValue($value)
    {
        $rootPath = Filesystem::getRootPath();
        $additionalPaths = [];
        foreach (\explode("\n", $value) as $additionalPath) {
            $additionalPath = \trim($additionalPath);
            if (!$additionalPath || strncmp($additionalPath, '#', strlen('#')) === 0) {
                $additionalPaths[] = $additionalPath;

                continue;
            }
            if (\preg_match('~^(.+?)(?: ([A-Z]+))?$~', $additionalPath, $matches) === 0) {
                continue;
            }
            [, $path, $flags] = \array_pad($matches, 3, '');
            if (\realpath($path) === \false) {
                \add_settings_error('staatic-settings', 'invalid_additional_path', \sprintf(
                    /* translators: %s: Supplied additional path. */
                    \__('The supplied additional path "%s" is not readable and therefore skipped', 'staatic'),
                    \esc_html($path)
                ));
                $additionalPaths[] = \sprintf('#%s', $additionalPath);

                continue;
            }
            $normalizedPath = Filesystem::normalizePath($path);
            if (\preg_match('~^' . \preg_quote($rootPath, '~') . '~i', $normalizedPath) === 0) {
                \add_settings_error('staatic-settings', 'invalid_additional_path', \sprintf(
                    /* translators: %s: Supplied additional path. */
                    \__('The supplied additional path "%s" is not web accessible and therefore skipped', 'staatic'),
                    \esc_html($path)
                ));
                $additionalPaths[] = \sprintf('#%s', $additionalPath);

                continue;
            }
            if (\untrailingslashit($normalizedPath) === $rootPath) {
                \add_settings_error('staatic-settings', 'invalid_additional_path', \sprintf(
                    /* translators: %s: Supplied additional path. */
                    \__('The supplied additional path "%s" is the WordPress installation directory and therefore skipped', 'staatic'),
                    \esc_html($path)
                ));
                $additionalPaths[] = \sprintf('#%s', $additionalPath);

                continue;
            }
            if (!\in_array($additionalPath, $additionalPaths)) {
                $additionalPaths[] = $additionalPath;
            }
        }

        return \implode("\n", $additionalPaths);
    }

    /**
     * @param mixed[] $attributes
     */
    public function render($attributes = []) : void
    {
        $this->renderer->render('admin/settings/additional_paths.php', [
            'setting' => $this,
            'attributes' => $attributes
        ]);
    }

    /**
     * @param string|null $value
     */
    public static function resolvedValue($value) : array
    {
        $resolvedValue = [];
        if ($value === null) {
            return $resolvedValue;
        }
        foreach (\explode("\n", $value) as $additionalPath) {
            $additionalPath = \trim($additionalPath);
            if (!$additionalPath || strncmp($additionalPath, '#', strlen('#')) === 0) {
                continue;
            }
            if (\preg_match('~^(.+?)(?: ([A-Z]+))?$~', $additionalPath, $matches) === 0) {
                continue;
            }
            [, $path, $flags] = \array_pad($matches, 3, '');
            if (!\is_readable($path)) {
                continue;
            }
            $dontTouch = $flags && strpos($flags, 'T') !== false;
            $dontFollow = $flags && strpos($flags, 'F') !== false;
            $dontSave = $flags && strpos($flags, 'S') !== false;
            $resolvedValue[$path] = \compact('path', 'dontTouch', 'dontFollow', 'dontSave');
        }

        return $resolvedValue;
    }
}
