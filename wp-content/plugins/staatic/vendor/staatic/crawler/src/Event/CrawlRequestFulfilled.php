<?php

namespace Staatic\Crawler\Event;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
use Staatic\Crawler\CrawlUrl;
class CrawlRequestFulfilled implements EventInterface
{
    /**
     * @var UriInterface
     */
    private $url;
    /**
     * @var UriInterface
     */
    private $transformedUrl;
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var UriInterface|null
     */
    private $foundOnUrl;
    /**
     * @var mixed[]
     */
    private $tags = [];
    public function __construct(UriInterface $url, UriInterface $transformedUrl, ResponseInterface $response, $foundOnUrl = null, array $tags = [])
    {
        $this->url = $url;
        $this->transformedUrl = $transformedUrl;
        $this->response = $response;
        $this->foundOnUrl = $foundOnUrl;
        $this->tags = $tags;
    }
    /**
     * @param CrawlUrl $crawlUrl
     */
    public static function create($crawlUrl) : self
    {
        return new self($crawlUrl->url(), $crawlUrl->transformedUrl(), $crawlUrl->response(), $crawlUrl->foundOnUrl(), $crawlUrl->tags());
    }
    public function url() : UriInterface
    {
        return $this->url;
    }
    public function transformedUrl() : UriInterface
    {
        return $this->transformedUrl;
    }
    public function response() : ResponseInterface
    {
        return $this->response;
    }
    public function foundOnUrl()
    {
        return $this->foundOnUrl;
    }
    public function tags() : array
    {
        return $this->tags;
    }
}
