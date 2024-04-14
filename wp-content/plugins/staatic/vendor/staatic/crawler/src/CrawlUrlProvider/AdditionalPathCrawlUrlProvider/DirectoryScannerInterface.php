<?php

namespace Staatic\Crawler\CrawlUrlProvider\AdditionalPathCrawlUrlProvider;

interface DirectoryScannerInterface
{
    /**
     * @param mixed[] $excludePaths
     */
    public function setExcludePaths($excludePaths) : void;
    /**
     * @param string $directory
     */
    public function scan($directory) : iterable;
}
