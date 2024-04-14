<?php

declare(strict_types=1);

namespace Staatic\WordPress\Setting\Build;

use Staatic\Vendor\GuzzleHttp\Psr7\Exception\MalformedUriException;
use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\WordPress\Setting\AbstractSetting;

final class AdditionalRedirectsSetting extends AbstractSetting
{
    public function name() : string
    {
        return 'staatic_additional_redirects';
    }

    public function type() : string
    {
        return self::TYPE_STRING;
    }

    public function label() : string
    {
        return \__('Additional Redirects', 'staatic');
    }

    public function description() : ?string
    {
        return \sprintf(
            /* translators: %s: Example additional redirects. */
            \__('Optionally add redirects that need to be included in the build.<br>%s', 'staatic'),
            $this->examplesList(
                ['/old-post /new-post 301', '/some-other-post https://othersite.example/some-other-post 302']
            )
        );
    }

    public function sanitizeValue($value)
    {
        $additionalRedirects = [];
        foreach (\explode("\n", $value) as $additionalRedirect) {
            $additionalRedirect = \trim($additionalRedirect);
            if (!$additionalRedirect || strncmp($additionalRedirect, '#', strlen('#')) === 0) {
                $additionalRedirects[] = $additionalRedirect;

                continue;
            }
            [$origin, $redirectUrl, $statusCode] = \array_pad(\explode(' ', $additionalRedirect, 3), 3, null);

            try {
                $originUrl = new Uri($origin);
            } catch (MalformedUriException $exception) {
                \add_settings_error('staatic-settings', 'invalid_additional_redirect', \sprintf(
                    /* translators: %s: Redirect origin. */
                    \__('The supplied additional redirect "%s" has a malformed origin and was therefore skipped', 'staatic'),
                    \esc_html($origin)
                ));
                $additionalRedirects[] = \sprintf('#%s', $additionalRedirect);

                continue;
            }
            if ($originUrl->getScheme() || $originUrl->getAuthority()) {
                \add_settings_error('staatic-settings', 'invalid_additional_redirect', \sprintf(
                    /* translators: %s: Redirect origin. */
                    \__('The supplied additional redirect "%s" should not contain the scheme or authority in the origin and was therefore skipped', 'staatic'),
                    \esc_html($origin)
                ));
                $additionalRedirects[] = \sprintf('#%s', $additionalRedirect);

                continue;
            }
            if (strncmp($originUrl->getPath(), '/', strlen('/')) !== 0) {
                \add_settings_error('staatic-settings', 'invalid_additional_redirect', \sprintf(
                    /* translators: %s: Redirect origin. */
                    \__('The supplied additional redirect "%s" does not have an absolute origin path and was therefore skipped', 'staatic'),
                    \esc_html($origin)
                ));
                $additionalRedirects[] = \sprintf('#%s', $additionalRedirect);

                continue;
            }

            try {
                $redirectUrl = new Uri($redirectUrl);
            } catch (MalformedUriException $exception) {
                \add_settings_error('staatic-settings', 'invalid_additional_redirect', \sprintf(
                    /* translators: %s: Redirect origin. */
                    \__('The supplied additional redirect "%s" has a malformed redirect URL and was therefore skipped', 'staatic'),
                    \esc_html($origin)
                ));
                $additionalRedirects[] = \sprintf('#%s', $additionalRedirect);

                continue;
            }
            if ($statusCode && !\in_array($statusCode, [301, 302, 307, 308])) {
                \add_settings_error('staatic-settings', 'invalid_additional_redirect', \sprintf(
                    /* translators: 1: Redirect origin, 2: HTTP status code. */
                    \__('The supplied additional redirect "%1$s" has an invalid HTTP status code "%2$s" and was therefore skipped', 'staatic'),
                    $origin,
                    \esc_html($statusCode)
                ));
                $additionalRedirects[] = \sprintf('#%s', $additionalRedirect);

                continue;
            }
            if (!\in_array($additionalRedirect, $additionalRedirects)) {
                $additionalRedirects[] = $additionalRedirect;
            }
        }

        return \implode("\n", $additionalRedirects);
    }

    /**
     * @param string|null $value
     */
    public static function resolvedValue($value) : array
    {
        if ($value === null) {
            return [];
        }
        $resolvedValue = [];
        foreach (\explode("\n", $value) as $additionalRedirect) {
            $additionalRedirect = \trim($additionalRedirect);
            if (!$additionalRedirect || strncmp($additionalRedirect, '#', strlen('#')) === 0) {
                continue;
            }
            [$origin, $redirectUrl, $statusCode] = \array_pad(\explode(' ', $additionalRedirect, 3), 3, null);
            $statusCode = $statusCode ? (int) $statusCode : 302;
            $resolvedValue[$origin] = \compact('redirectUrl', 'statusCode');
        }

        return $resolvedValue;
    }

    /**
     * @param mixed[] $attributes
     */
    public function render($attributes = []) : void
    {
        $this->renderer->render('admin/settings/additional_redirects.php', [
            'setting' => $this,
            'attributes' => $attributes
        ]);
    }
}
