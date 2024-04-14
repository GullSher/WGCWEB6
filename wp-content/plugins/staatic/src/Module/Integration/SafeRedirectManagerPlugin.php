<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Integration;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use SRM_Redirect;
use Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider;
use Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider\AdditionalUrl;
use Staatic\Crawler\CrawlUrlProvider\CrawlUrlProviderCollection;
use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Publication\Publication;

final class SafeRedirectManagerPlugin implements ModuleInterface
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
        $redirects = srm_get_redirects([
            'posts_per_page' => srm_get_max_redirects(),
            'post_status' => 'publish'
        ]);
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
        if ($item['enable_regex']) {
            return \false;
        }
        if (\strstr($item['redirect_from'], '*') !== \false) {
            return \false;
        }
        if (!\in_array($item['status_code'], [301, 302, 307, 308])) {
            return \false;
        }

        return \true;
    }

    private function itemToAdditionalUrl(array $item) : AdditionalUrl
    {
        $url = new Uri($item['redirect_from']);
        $url = UriResolver::resolve($this->baseUrl, $url);

        return new AdditionalUrl($url);
    }

    private function isPluginActive() : bool
    {
        if (!\class_exists(SRM_Redirect::class)) {
            return \false;
        }
        if (!\function_exists('Staatic\\Vendor\\srm_get_redirects')) {
            return \false;
        }
        if (!\function_exists('Staatic\\Vendor\\srm_get_max_redirects')) {
            return \false;
        }

        return \true;
    }
}
