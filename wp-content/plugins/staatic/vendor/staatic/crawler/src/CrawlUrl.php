<?php

namespace Staatic\Crawler;

use InvalidArgumentException;
use LogicException;
use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Ramsey\Uuid\Uuid;
final class CrawlUrl
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var UriInterface
     */
    private $url;
    /**
     * @var UriInterface
     */
    private $originUrl;
    /**
     * @var UriInterface|null
     */
    private $foundOnUrl;
    /**
     * @var int
     */
    private $depthLevel = 0;
    /**
     * @var int
     */
    private $redirectLevel = 0;
    /**
     * @var mixed[]
     */
    private $tags = [];
    /**
     * @var UriInterface|null
     */
    private $transformedUrl;
    /**
     * @var ResponseInterface|null
     */
    private $response;
    public function __construct(string $id, UriInterface $url, UriInterface $originUrl, $foundOnUrl = null, int $depthLevel = 0, int $redirectLevel = 0, array $tags = [], $transformedUrl = null)
    {
        $this->id = $id;
        $this->url = $url;
        $this->originUrl = $originUrl;
        $this->foundOnUrl = $foundOnUrl;
        $this->depthLevel = $depthLevel;
        $this->redirectLevel = $redirectLevel;
        $this->tags = $tags;
        $this->transformedUrl = $transformedUrl;
        if (!$url->getHost()) {
            throw new InvalidArgumentException("The URL should be absolute: '{$url}'.");
        }
    }
    public static function create(UriInterface $url, self $parentCrawlUrl = null, bool $isRedirected = \false, array $tags = [], $transformedUrl = null) : self
    {
        if ($isRedirected && $parentCrawlUrl === null) {
            throw new LogicException('A redirected crawl URL requires parent crawl URL.');
        }
        return new static((string) Uuid::uuid5(Uuid::NAMESPACE_URL, (string) $url), $url, $isRedirected ? $parentCrawlUrl->originUrl() : $url, $parentCrawlUrl ? $parentCrawlUrl->url() : null, $parentCrawlUrl ? $parentCrawlUrl->depthLevel() + 1 : 0, $isRedirected ? $parentCrawlUrl->redirectLevel() + 1 : 0, $tags, $transformedUrl);
    }
    public function id() : string
    {
        return $this->id;
    }
    public function url() : UriInterface
    {
        return $this->url;
    }
    public function originUrl() : UriInterface
    {
        return $this->originUrl;
    }
    public function transformedUrl()
    {
        return $this->transformedUrl;
    }
    public function withTransformedUrl($transformedUrl) : self
    {
        $newCrawlUrl = clone $this;
        $newCrawlUrl->transformedUrl = $transformedUrl;
        return $newCrawlUrl;
    }
    public function foundOnUrl()
    {
        return $this->foundOnUrl;
    }
    public function depthLevel() : int
    {
        return $this->depthLevel;
    }
    public function redirectLevel() : int
    {
        return $this->redirectLevel;
    }
    public function tags() : array
    {
        return $this->tags;
    }
    public function hasTag(string $tag) : bool
    {
        return \in_array($tag, $this->tags);
    }
    public function withTags(array $tags) : self
    {
        $newCrawlUrl = clone $this;
        $newCrawlUrl->tags = $tags;
        return $newCrawlUrl;
    }
    public function response()
    {
        return $this->response;
    }
    public function withResponse($response) : self
    {
        $newCrawlUrl = clone $this;
        $newCrawlUrl->response = $response;
        return $newCrawlUrl;
    }
}
