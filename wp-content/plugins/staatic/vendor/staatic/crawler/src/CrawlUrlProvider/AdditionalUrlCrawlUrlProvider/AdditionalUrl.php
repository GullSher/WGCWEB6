<?php

namespace Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\CrawlerInterface;
use Staatic\Crawler\CrawlUrl;
class AdditionalUrl
{
    /**
     * @var UriInterface
     */
    private $url;
    /**
     * @var string
     */
    private $priority = self::PRIORITY_NORMAL;
    /**
     * @var bool
     */
    private $dontTouch = \false;
    /**
     * @var bool
     */
    private $dontFollow = \false;
    /**
     * @var bool
     */
    private $dontSave = \false;
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_LOW = 'low';
    public function __construct(UriInterface $url, string $priority = self::PRIORITY_NORMAL, bool $dontTouch = \false, bool $dontFollow = \false, bool $dontSave = \false)
    {
        $this->url = $url;
        $this->priority = $priority;
        $this->dontTouch = $dontTouch;
        $this->dontFollow = $dontFollow;
        $this->dontSave = $dontSave;
    }
    public function createCrawlUrl() : CrawlUrl
    {
        return CrawlUrl::create($this->url, null, false, $this->tags());
    }
    public function url() : UriInterface
    {
        return $this->url;
    }
    public function priority() : string
    {
        return $this->priority;
    }
    public function dontTouch() : bool
    {
        return $this->dontTouch;
    }
    public function dontFollow() : bool
    {
        return $this->dontFollow;
    }
    public function dontSave() : bool
    {
        return $this->dontSave;
    }
    private function tags() : array
    {
        $tags = [];
        if ($this->priority === self::PRIORITY_HIGH) {
            $tags[] = CrawlerInterface::TAG_PRIORITY_HIGH;
        } elseif ($this->priority === self::PRIORITY_LOW) {
            $tags[] = CrawlerInterface::TAG_PRIORITY_LOW;
        }
        if ($this->dontTouch) {
            $tags[] = CrawlerInterface::TAG_DONT_TOUCH;
        }
        if ($this->dontFollow) {
            $tags[] = CrawlerInterface::TAG_DONT_FOLLOW;
        }
        if ($this->dontSave) {
            $tags[] = CrawlerInterface::TAG_DONT_SAVE;
        }
        return $tags;
    }
}
