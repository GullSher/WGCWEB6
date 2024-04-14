<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Integration;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider;
use Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider\AdditionalUrl;
use Staatic\Crawler\CrawlUrlProvider\CrawlUrlProviderCollection;
use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Publication\Publication;
use WPSEO_Redirect_Manager;

final class YoastPremiumPlugin implements ModuleInterface
{
    /**
     * @var UriInterface
     */
    private $baseUrl;

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
        $redirects = \get_option('wpseo-premium-redirects-base');
        if (!\is_array($redirects) || empty($redirects)) {
            return $providers;
        }
        $additionalUrls = \array_filter($redirects, function ($item) {
            return $this->shouldInclude($item);
        });
        if (empty($additionalUrls)) {
            return $providers;
        }
        $additionalUrls = \array_map(function (array $item) {
            return $this->itemToAdditionalUrl($item);
        }, $additionalUrls);
        $providers->addProvider(new AdditionalUrlCrawlUrlProvider($additionalUrls));

        return $providers;
    }

    private function shouldInclude(array $item) : bool
    {
        if ($item['format'] !== 'plain') {
            return \false;
        }
        if (!\in_array($item['type'], [301, 302, 307, 308])) {
            return \false;
        }

        return \true;
    }

    private function itemToAdditionalUrl(array $item) : AdditionalUrl
    {
        $url = new Uri($item['origin']);
        $url = UriResolver::resolve($this->baseUrl, $url);

        return new AdditionalUrl($url);
    }

    private function isPluginActive() : bool
    {
        return \class_exists(WPSEO_Redirect_Manager::class);
    }
}
