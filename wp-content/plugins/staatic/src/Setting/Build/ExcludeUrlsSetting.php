<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Build;

use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\WordPress\Service\SiteUrlProvider;
use Staatic\WordPress\Setting\AbstractSetting;

final class ExcludeUrlsSetting extends AbstractSetting
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
        return 'staatic_exclude_urls';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    public function label() : string
    {
        return \__('Excluded URLs', 'staatic');
    }

    public function description() : ?string
    {
        $examples = \array_map(function (string $example) {
            return \rtrim($this->basePath(), '/') . $example;
        }, ['/excluded-page/', '/excluded-page/*', '~^/excluded-page-[1-3]/~']);

        return \sprintf(
            /* translators: %s: Example exclude URLs. */
            \__('Optionally add URLs that need to be excluded from the build.<br>%s', 'staatic'),
            $this->examplesList($examples)
        );
    }

    public function defaultValue()
    {
        return \implode("\n", ['/wp-json/*']);
    }

    public function sanitizeValue($value)
    {
        $siteUrl = ($this->siteUrlProvider)();
        $excludeUrls = [];
        foreach (\explode("\n", $value) as $excludeUrl) {
            $excludeUrl = \trim($excludeUrl);
            if (!$excludeUrl || strncmp($excludeUrl, '#', strlen('#')) === 0) {
                $excludeUrls[] = $excludeUrl;

                continue;
            }
            $authority = (new Uri($excludeUrl))->getAuthority();
            if ($authority && $authority !== $siteUrl->getAuthority()) {
                \add_settings_error('staatic-settings', 'invalid_exclude_url', \sprintf(
                    /* translators: %s: Supplied excluded URL. */
                    \__('The supplied excluded URL "%s" is not part of this site and therefore skipped', 'staatic'),
                    \esc_html($excludeUrl)
                ));
                $excludeUrls[] = \sprintf('#%s', $excludeUrl);

                continue;
            }
            if (!\in_array($excludeUrl, $excludeUrls)) {
                $excludeUrls[] = $excludeUrl;
            }
        }

        return \implode("\n", $excludeUrls);
    }

    /**
     * @param string|null $value
     */
    public static function resolvedValue($value)
    {
        $resolvedValue = [];
        if ($value === null) {
            return $resolvedValue;
        }
        foreach (\explode("\n", $value) as $excludeUrl) {
            if (!$excludeUrl || strncmp($excludeUrl, '#', strlen('#')) === 0) {
                continue;
            }
            $resolvedValue[] = $excludeUrl;
        }

        return $resolvedValue;
    }

    /**
     * @param mixed[] $attributes
     */
    public function render($attributes = []) : void
    {
        $this->renderer->render('admin/settings/excluded_urls.php', [
            'setting' => $this,
            'attributes' => $attributes
        ]);
    }

    private function basePath() : string
    {
        return ($this->siteUrlProvider)()->getPath();
    }
}
