<?php

namespace Staatic\Crawler\CrawlUrlProvider;

use Generator;
use InvalidArgumentException;
use Staatic\Crawler\CrawlUrl;
use Staatic\Crawler\CrawlerInterface;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\CrawlUrlProvider\AdditionalPathCrawlUrlProvider\DirectoryScannerInterface;
use Staatic\Crawler\CrawlUrlProvider\AdditionalPathCrawlUrlProvider\StandardDirectoryScanner;
final class AdditionalPathCrawlUrlProvider implements CrawlUrlProviderInterface
{
    /**
     * @var UriInterface
     */
    private $baseUrl;
    /**
     * @var string
     */
    private $basePath;
    /**
     * @var string
     */
    private $path;
    /**
     * @var DirectoryScannerInterface
     */
    private $directoryScanner;
    /**
     * @var mixed[]
     */
    private $tags = [];
    public function __construct(UriInterface $baseUrl, string $basePath, string $path, array $excludePaths = [], bool $dontTouch = \false, bool $dontFollow = \false, bool $dontSave = \false, $directoryScanner = null)
    {
        $this->baseUrl = $baseUrl;
        $this->basePath = $basePath;
        $this->path = $path;
        $this->basePath = \rtrim($basePath, '/\\');
        $this->directoryScanner = $directoryScanner ?: new StandardDirectoryScanner();
        $this->directoryScanner->setExcludePaths($excludePaths);
        if ($dontTouch) {
            $this->tags[] = CrawlerInterface::TAG_DONT_TOUCH;
        }
        if ($dontFollow) {
            $this->tags[] = CrawlerInterface::TAG_DONT_FOLLOW;
        }
        if ($dontSave) {
            $this->tags[] = CrawlerInterface::TAG_DONT_SAVE;
        }
    }
    public function provide() : Generator
    {
        if (\is_file($this->path)) {
            (yield $this->convertPathToCrawlUrl($this->path));
        } elseif (\is_dir($this->path)) {
            $paths = $this->directoryScanner->scan($this->path);
            foreach ($paths as $path) {
                (yield $this->convertPathToCrawlUrl($path));
            }
        }
    }
    private function convertPathToCrawlUrl(string $path) : CrawlUrl
    {
        $path = \DIRECTORY_SEPARATOR === '/' ? $path : \str_replace(\DIRECTORY_SEPARATOR, '/', $path);
        $url = $this->convertPathToUrl($path);
        return CrawlUrl::create($url, null, false, $this->tags);
    }
    private function convertPathToUrl(string $path) : UriInterface
    {
        if (strncmp($path, $this->basePath, strlen($this->basePath)) !== 0) {
            throw new InvalidArgumentException("Path '{$path}' does not start with expected base path '{$this->basePath}'");
        }
        return $this->baseUrl->withPath(\mb_substr($path, \mb_strlen($this->basePath)));
    }
}
