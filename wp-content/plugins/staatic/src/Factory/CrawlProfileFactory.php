<?php

declare(strict_types=1);

namespace Staatic\WordPress\Factory;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
use RuntimeException;
use Staatic\Crawler\CrawlProfile\CrawlProfileInterface;
use Staatic\Crawler\UrlEvaluator\CallbackEvaluator;
use Staatic\Crawler\UrlEvaluator\ChainUrlEvaluator;
use Staatic\Crawler\UrlEvaluator\ExcludeRulesUrlEvaluator;
use Staatic\Crawler\UrlEvaluator\InternalUrlEvaluator;
use Staatic\Crawler\UrlEvaluator\UrlEvaluatorInterface;
use Staatic\WordPress\Bridge\CrawlProfile;
use Staatic\WordPress\Setting\Build\ExcludeUrlsSetting;

final class CrawlProfileFactory
{
    /**
     * @var ExcludeUrlsSetting
     */
    private $excludeUrls;

    public function __construct(ExcludeUrlsSetting $excludeUrls)
    {
        $this->excludeUrls = $excludeUrls;
    }

    public function __invoke(UriInterface $baseUrl, UriInterface $destinationUrl) : CrawlProfileInterface
    {
        $urlEvaluator = $this->createUrlEvaluator($baseUrl);

        return new CrawlProfile($baseUrl, $destinationUrl, $urlEvaluator);
    }

    private function createUrlEvaluator(UriInterface $baseUrl) : UrlEvaluatorInterface
    {
        $evaluators = [new InternalUrlEvaluator($baseUrl)];
        $excludeUrls = ExcludeUrlsSetting::resolvedValue($this->excludeUrls->value());
        $excludeUrls = $this->addBaseExcludeUrls($excludeUrls);
        $excludeUrls = \apply_filters('staatic_exclude_urls', $excludeUrls, $baseUrl);
        if (\count($excludeUrls)) {
            $evaluators[] = new ExcludeRulesUrlEvaluator($excludeUrls);
        }
        if (\has_filter('staatic_should_crawl_url')) {
            $evaluators[] = new CallbackEvaluator(function (UriInterface $resolvedUrl, array $context = []) {
                return (bool) \apply_filters('staatic_should_crawl_url', \true, $resolvedUrl, $context);
            });
        }
        $evaluators = \array_values(\apply_filters('staatic_url_evaluators', $evaluators));
        if (empty($evaluators)) {
            throw new RuntimeException('No URL evaluators configured.');
        }

        return \count($evaluators) > 1 ? new ChainUrlEvaluator($evaluators) : $evaluators[0];
    }

    private function addBaseExcludeUrls(array $excludeUrls) : array
    {
        return \array_merge(
            $excludeUrls,
            ['~^/(xmlrpc|wp-comments-post|wp-login)\\.php~', '~/wp-admin/?$~', '~/\\?p=\\d+~']
        );
    }
}
