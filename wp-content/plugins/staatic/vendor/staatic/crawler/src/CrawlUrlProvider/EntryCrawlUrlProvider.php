<?php

namespace Staatic\Crawler\CrawlUrlProvider;

use Generator;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\CrawlUrl;
class EntryCrawlUrlProvider implements CrawlUrlProviderInterface
{
    /**
     * @var UriInterface
     */
    private $url;
    public function __construct(UriInterface $url)
    {
        $this->url = $url;
    }
    public function provide() : Generator
    {
        (yield CrawlUrl::create($this->url));
    }
}
