<?php

declare(strict_types=1);

namespace Staatic\WordPress\Service;

use Error;
use FilesystemIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

final class Filesystem
{
    public function isDirectory(string $directory) : bool
    {
        return \is_dir($directory);
    }

    public function ensureDirectoryExists(string $directory) : void
    {
        if (\is_dir($directory)) {
            return;
        }

        try {
            \mkdir($directory, 0777, \true);
        } catch (Error $error) {
            throw new RuntimeException("Unable to create directory {$directory}: {$error->getMessage()}");
        }
    }

    public function clearDirectory(string $directory) : void
    {
        if (!\is_dir($directory)) {
            throw new InvalidArgumentException("Directory does not exist in {$directory}");
        }
        $deleteIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
            $directory,
            FilesystemIterator::SKIP_DOTS
        ), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($deleteIterator as $file) {
            $file = (string) $file;
            if (\is_dir($file)) {
                $this->removeDirectory($file);
            } elseif (\is_file($file)) {
                $this->removeFile($file);
            }
        }
    }

    public function removeDirectory(string $path) : void
    {
        try {
            \rmdir($path);
        } catch (Error $error) {
            throw new RuntimeException("Directory could not be removed in {$path}: {$error->getMessage()}");
        }
    }

    public function removeFile(string $path) : void
    {
        try {
            \unlink($path);
        } catch (Error $error) {
            throw new RuntimeException("File could not be removed in {$path}: {$error->getMessage()}");
        }
    }

    public static function normalizePath(string $path) : string
    {
        return \wp_normalize_path($path);
    }

    // e.g. "/var/www/html"
    public static function getRootPath() : string
    {
        return \untrailingslashit(self::normalizePath(\ABSPATH));
    }

    // e.g. "/home/wordpress/domains/wordpress/public_html/wp-content/uploads/sites/3/"
    // or "C:/wordpress/htdocs/wp-content/uploads/sites/3/"
    public static function getUploadsPath() : string
    {
        $uploadsDirectory = \wp_upload_dir(null, \false);
        $uploadsDirectory = $uploadsDirectory['basedir'];

        return self::normalizePath(\trailingslashit($uploadsDirectory));
    }

    // e.g. "/wp-content/uploads/sites/3/"
    public static function getRelativeUploadsPath() : string
    {
        return \str_replace(self::getRootPath(), '', self::getUploadsPath());
    }
}
