<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Build;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Crawler\CrawlUrlProvider\AdditionalUrlCrawlUrlProvider\AdditionalUrl;
use Staatic\WordPress\Service\SiteUrlProvider;
use Staatic\WordPress\Setting\AbstractSetting;

final class AdditionalUrlsSetting extends AbstractSetting
{
    /**
     * @var SiteUrlProvider
     */
    private $siteUrlProvider;

    public function __construct(SiteUrlProvider $siteUrlProvider)
    {
        $this->siteUrlProvider = $siteUrlProvider;
    }

    public function name() : string
    {
        return 'staatic_additional_urls';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    public function label() : string
    {
        return \__('Additional URLs', 'staatic');
    }

    public function description() : ?string
    {
        $examples = \array_map(function (string $example) {
            return \rtrim($this->basePath(), '/') . $example;
        }, ['/favicon.ico', '/robots.txt', '/wp-sitemap.xml']);

        return \sprintf(
            /* translators: %s: Example additional URLs. */
            \__('Optionally add (absolute or relative) URLs that need to be included in the build.<br>%s', 'staatic'),
            $this->examplesList($examples)
        );
    }

    public function defaultValue()
    {
        $value = [\rtrim($this->basePath(), '/') . '/wp-sitemap.xml'];
        if (!$this->basePath() || $this->basePath() === '/') {
            $value[] = '/robots.txt';
        }

        return \implode("\n", $value);
    }

    public function sanitizeValue($value)
    {
        $siteUrl = ($this->siteUrlProvider)();
        $additionalUrls = [];
        foreach (\explode("\n", $value) as $additionalUrl) {
            $additionalUrl = \trim($additionalUrl);
            if (!$additionalUrl || strncmp($additionalUrl, '#', strlen('#')) === 0) {
                $additionalUrls[] = $additionalUrl;

                continue;
            }
            $authority = (new Uri($additionalUrl))->getAuthority();
            if ($authority && $authority !== $siteUrl->getAuthority()) {
                \add_settings_error('staatic-settings', 'invalid_additional_url', \sprintf(
                    /* translators: %s: Supplied additional URL. */
                    \__('The supplied additional URL "%s" is not part of this site and therefore skipped', 'staatic'),
                    \esc_html($additionalUrl)
                ));
                $additionalUrls[] = \sprintf('#%s', $additionalUrl);

                continue;
            }
            if (!\in_array($additionalUrl, $additionalUrls)) {
                $additionalUrls[] = $additionalUrl;
            }
        }

        return \implode("\n", $additionalUrls);
    }

    /**
     * @param mixed[] $attributes
     */
    public function render($attributes = []) : void
    {
        $this->renderer->render('admin/settings/additional_urls.php', [
            'setting' => $this,
            'attributes' => $attributes
        ]);
    }

    /** @return AdditionalUrl[]
     * @param string|null $value
     * @param UriInterface $baseUrl */
    public static function resolvedValue($value, $baseUrl) : array
    {
        $resolvedValue = [];
        if ($value === null) {
            return $resolvedValue;
        }
        foreach (\explode("\n", $value) as $additionalUrl) {
            $additionalUrl = \trim($additionalUrl);
            if (!$additionalUrl || strncmp($additionalUrl, '#', strlen('#')) === 0) {
                continue;
            }
            if (\preg_match('~^(.+?)(?: ([A-Z]+))?$~', $additionalUrl, $matches) === 0) {
                continue;
            }
            [, $url, $flags] = \array_pad($matches, 3, '');
            $url = UriResolver::resolve($baseUrl, new Uri($url));
            $dontTouch = $flags && strpos($flags, 'T') !== false;
            $dontFollow = $flags && strpos($flags, 'F') !== false;
            $dontSave = $flags && strpos($flags, 'S') !== false;
            $resolvedValue[(string) $url] = new AdditionalUrl($url, 'normal', $dontTouch, $dontFollow, $dontSave);
        }

        return $resolvedValue;
    }

    private function basePath() : string
    {
        return ($this->siteUrlProvider)()->getPath();
    }
}
