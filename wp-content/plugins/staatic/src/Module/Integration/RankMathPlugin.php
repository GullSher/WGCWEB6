<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Integration;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use RankMath\Redirections\DB;
use Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider;
use Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider\AdditionalUrl;
use Staatic\Crawler\CrawlUrlProvider\CrawlUrlProviderCollection;
use Staatic\WordPress\Module\ModuleInterface;
use Staatic\WordPress\Publication\Publication;

final class RankMathPlugin implements ModuleInterface
{
    private const REDIRECT_LIMIT = 1000;

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
        $additionalUrls = $this->fetchRedirectUrls();
        if (empty($additionalUrls)) {
            return $providers;
        }
        $providers->addProvider(new AdditionalUrlCrawlUrlProvider($additionalUrls));

        return $providers;
    }

    /** @return iterable<AdditionalUrl> */
    private function fetchRedirectUrls() : iterable
    {
        $result = DB::get_redirections([
            'status' => 'active',
            'limit' => self::REDIRECT_LIMIT
        ]);
        $redirects = $result['redirections'] ?? [];
        if (!\is_array($redirects) || empty($redirects)) {
            return [];
        }
        foreach ($redirects as $redirect) {
            if (!\in_array($redirect['header_code'], [301, 302, 307, 308])) {
                continue;
            }
            $sources = \maybe_unserialize($redirect['sources']);
            foreach ($sources as $source) {
                if ($source['comparison'] !== 'exact') {
                    continue;
                }
                $url = new Uri($source['pattern']);
                $url = UriResolver::resolve($this->baseUrl, $url);
                (yield new AdditionalUrl($url));
            }
        }
    }

    private function isPluginActive() : bool
    {
        return \class_exists(DB::class);
    }
}
