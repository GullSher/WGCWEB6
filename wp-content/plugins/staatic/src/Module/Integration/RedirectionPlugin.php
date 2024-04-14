<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Integration;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Red_Item;
use Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider;
use Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider\AdditionalUrl;
use Staatic\Crawler\CrawlUrlProvider\CrawlUrlProviderCollection;
use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Publication\Publication;

final class RedirectionPlugin implements ModuleInterface
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
        $additionalUrls = \array_filter(Red_Item::get_all(), function (Red_Item $item) {
            return $this->shouldInclude($item);
        });
        if (empty($additionalUrls)) {
            return $providers;
        }
        $additionalUrls = \array_map(function (Red_Item $item) {
            return $this->itemToAdditionalUrl($item);
        }, $additionalUrls);
        $providers->addProvider(new AdditionalUrlCrawlUrlProvider($additionalUrls));

        return $providers;
    }

    private function shouldInclude(Red_Item $item) : bool
    {
        if (!$item->is_enabled()) {
            return \false;
        }
        if ($item->is_dynamic() || $item->is_regex()) {
            return \false;
        }
        if ($item->get_match_type() !== 'url') {
            return \false;
        }
        if ($item->get_action_type() !== 'url') {
            return \false;
        }

        return \true;
    }

    private function itemToAdditionalUrl(Red_Item $item) : AdditionalUrl
    {
        $url = new Uri($item->get_url());
        $url = UriResolver::resolve($this->baseUrl, $url);

        return new AdditionalUrl($url);
    }

    private function isPluginActive() : bool
    {
        if (!\class_exists(Red_Item::class)) {
            return \false;
        }
        if (!\method_exists(Red_Item::class, 'get_all')) {
            return \false;
        }

        return \true;
    }
}
