<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Integration;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Simple301Redirects;
use Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider;
use Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider\AdditionalUrl;
use Staatic\Crawler\CrawlUrlProvider\CrawlUrlProviderCollection;
use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Publication\Publication;

final class Simple301RedirectsPlugin implements ModuleInterface
{
    /**
     * @var UriInterface
     */
    private $baseUrl;

    /**
     * @var bool
     */
    private $wildcard;

    public function hooks() : void
    {
        \add_action('wp_loaded', [$this, 'setupIntegration']);
    }

    public function setupIntegration() : void
    {
        if (!$this->isPluginActive()) {
            return;
        }
        \add_filter('staatic_crawl_url_providers', [$this, 'registerCrawlUrlProvider'], 10, 2);
    }

    public function registerCrawlUrlProvider(
        CrawlUrlProviderCollection $providers,
        Publication $publication
    ) : CrawlUrlProviderCollection
    {
        $this->baseUrl = $publication->build()->entryUrl();
        $redirects = \get_option('301_redirects');
        if (!\is_array($redirects) || empty($redirects)) {
            return $providers;
        }
        $this->wildcard = \get_option('301_redirects_wildcard') === 'true';
        $additionalUrls = \array_filter(\array_keys($redirects), function ($origin) {
            return $this->shouldInclude($origin);
        });
        if (empty($additionalUrls)) {
            return $providers;
        }
        $additionalUrls = \array_map(function (string $origin) {
            return $this->originToAdditionalUrl($origin);
        }, $additionalUrls);
        $providers->addProvider(new AdditionalUrlCrawlUrlProvider($additionalUrls));

        return $providers;
    }

    private function shouldInclude(string $origin) : bool
    {
        if ($this->wildcard && \strpos($origin, '*') !== \false) {
            return \false;
        }

        return \true;
    }

    private function originToAdditionalUrl(string $origin) : AdditionalUrl
    {
        $url = new Uri($origin);
        $url = UriResolver::resolve($this->baseUrl, $url);

        return new AdditionalUrl($url);
    }

    private function isPluginActive() : bool
    {
        return \class_exists(Simple301Redirects::class);
    }
}
