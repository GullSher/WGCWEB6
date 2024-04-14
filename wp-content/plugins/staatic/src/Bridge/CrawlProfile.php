<?php

namespace Staatic\WordPress\Bridge;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\CrawlProfile\AbstractCrawlProfile;
use Staatic\Crawler\UrlEvaluator\UrlEvaluatorInterface;
use Staatic\Crawler\UrlNormalizer\InternalUrlNormalizer;
use Staatic\Crawler\UrlTransformer\OfflineUrlTransformer;
use Staatic\Crawler\UrlTransformer\StandardUrlTransformer;

final class CrawlProfile extends AbstractCrawlProfile
{
    public function __construct(UriInterface $baseUrl, UriInterface $destinationUrl, UrlEvaluatorInterface $urlEvaluator)
    {
        $this->baseUrl = $baseUrl;
        $this->destinationUrl = $destinationUrl;
        $this->urlEvaluator = $urlEvaluator;
        $this->urlNormalizer = new InternalUrlNormalizer();
        if ((string) $destinationUrl === '') {
            $this->urlTransformer = new OfflineUrlTransformer();
        } else {
            $this->urlTransformer = new StandardUrlTransformer($baseUrl, $destinationUrl);
        }
    }
}
