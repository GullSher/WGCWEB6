<?php

namespace Staatic\Crawler\CrawlUrlProvider;

use Generator;
use Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider\AdditionalUrl;
class AdditionalUrlCrawlUrlProvider implements CrawlUrlProviderInterface
{
    /**
     * @var iterable
     */
    private $additionalUrls;
    public function __construct(iterable $additionalUrls)
    {
        $this->additionalUrls = $additionalUrls;
    }
    public function provide() : Generator
    {
        foreach ($this->additionalUrls as $additionalUrl) {
            (yield $additionalUrl->createCrawlUrl());
        }
    }
}
