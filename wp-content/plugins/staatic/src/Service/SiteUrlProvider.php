<?php

declare(strict_types=1);

namespace Staatic\WordPress\Service;

use Staatic\Vendor\GuzzleHttp\Psr7\Exception\MalformedUriException;
use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\Psr\Http\Message\UriInterface;

final class SiteUrlProvider
{
    /**
     * @var bool
     */
    private $cached = \true;

    /**
     * @var UriInterface|null
     */
    private $siteUrl;

    public function __construct(bool $cached = \true)
    {
        $this->cached = $cached;
    }

    public function __invoke() : UriInterface
    {
        if (!$this->siteUrl || !$this->cached) {
            $this->siteUrl = $this->determineSiteUrl();
        }

        return $this->siteUrl;
    }

    private function determineSiteUrl() : UriInterface
    {
        $siteUrl = $_ENV['STAATIC_SITE_URL'] ?? $_SERVER['STAATIC_SITE_URL'] ?? \site_url();
        $siteUrl = \apply_filters('staatic_site_url', $siteUrl);

        try {
            $result = new Uri($siteUrl);
        } catch (MalformedUriException $e) {
            $this->handleError($e->getMessage(), $siteUrl);
        }

        try {
            // Make sure that the site URL ends with a slash to prevent unnecessary
            // redirect (WordPress usually redirects "/page" to "/page/").
            if (!$result->getPath()) {
                $result = $result->withPath('/');
            } elseif (substr_compare($result->getPath(), '/', -strlen('/')) !== 0) {
                $result = $result->withPath($result->getPath() . '/');
            }
        } catch (MalformedUriException $e) {
            $this->handleError($e->getMessage(), $siteUrl);
        }

        return $result;
    }

    private function handleError(string $message, string $siteUrl) : void
    {
        \wp_die(\sprintf(
            /* translators: 1: The resulting error message, 2: The detected site URL . */
            \__('<strong>Staatic was unable to determine a valid WordPress site URL</strong><br><br>Got "<strong>%2$s</strong>", which can result in the following error: <em>%1$s</em> - please check the following locations and ensure the configured site URL is valid:<br><ul><li>the value of <code>WP_SITEURL</code> in the <code>wp-config.php</code> file;</li><li>the value of <code>siteurl</code> in the <code>wp_options</code> database table;</li><li>the value of the <code>STAATIC_SITE_URL</code> environment variable;</li><li>any <code>staatic_site_url</code> or <code>site_url</code> filter hook implementation.</li></ul>', 'staatic'),
            $message,
            $siteUrl
        ));
    }
}
