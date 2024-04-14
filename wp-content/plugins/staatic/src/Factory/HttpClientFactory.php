<?php

declare(strict_types=1);

namespace Staatic\WordPress\Factory;

use Staatic\Vendor\GuzzleHttp\Client;
use Staatic\Vendor\GuzzleHttp\ClientInterface;
use Staatic\Vendor\GuzzleHttp\HandlerStack;
use Staatic\Vendor\GuzzleHttp\RequestOptions;
use Staatic\Vendor\GuzzleHttp\Utils as GuzzleUtils;
use Staatic\Vendor\GuzzleRetry\GuzzleRetryMiddleware;
use Staatic\WordPress\Bridge\HttpsToHttpMiddleware;
use Staatic\WordPress\Bridge\RewriteResponseBodyMiddleware;
use Staatic\WordPress\Service\SiteUrlProvider;
use Staatic\WordPress\Setting\Advanced\HttpAuthenticationPasswordSetting;
use Staatic\WordPress\Setting\Advanced\HttpAuthenticationUsernameSetting;
use Staatic\WordPress\Setting\Advanced\HttpConcurrencySetting;
use Staatic\WordPress\Setting\Advanced\HttpDelaySetting;
use Staatic\WordPress\Setting\Advanced\HttpTimeoutSetting;
use Staatic\WordPress\Setting\Advanced\HttpToHttpsSetting;
use Staatic\WordPress\Setting\Advanced\SslVerifyBehaviorSetting;
use Staatic\WordPress\Setting\Advanced\SslVerifyPathSetting;
use Staatic\WordPress\Util\HttpUtil;
use Staatic\Vendor\Symfony\Component\HttpClient\HttpClient;
use Staatic\Vendor\Symfony\Component\HttpClient\HttpOptions;
use Staatic\Vendor\Symfony\Component\HttpClient\RetryableHttpClient;
use Staatic\Vendor\Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientFactory
{
    /**
     * @var \Staatic\WordPress\Setting\Advanced\HttpConcurrencySetting
     */
    private $httpConcurrency;

    /**
     * @var \Staatic\WordPress\Setting\Advanced\HttpTimeoutSetting
     */
    private $httpTimeout;

    /**
     * @var \Staatic\WordPress\Setting\Advanced\HttpDelaySetting
     */
    private $httpDelay;

    /**
     * @var SslVerifyBehaviorSetting
     */
    private $sslVerifyBehavior;

    /**
     * @var \Staatic\WordPress\Setting\Advanced\SslVerifyPathSetting
     */
    private $sslVerifyPath;

    /**
     * @var \Staatic\WordPress\Setting\Advanced\HttpAuthenticationUsernameSetting
     */
    private $httpAuthUsername;

    /**
     * @var HttpAuthenticationPasswordSetting
     */
    private $httpAuthPassword;

    /**
     * @var \Staatic\WordPress\Setting\Advanced\HttpToHttpsSetting
     */
    private $httpToHttps;

    /**
     * @var SiteUrlProvider
     */
    private $siteUrlProvider;

    public function __construct(HttpConcurrencySetting $httpConcurrency, HttpTimeoutSetting $httpTimeout, HttpDelaySetting $httpDelay, SslVerifyBehaviorSetting $sslVerifyBehavior, SslVerifyPathSetting $sslVerifyPath, HttpAuthenticationUsernameSetting $httpAuthUsername, HttpAuthenticationPasswordSetting $httpAuthPassword, HttpToHttpsSetting $httpToHttps, SiteUrlProvider $siteUrlProvider)
    {
        $this->httpConcurrency = $httpConcurrency;
        $this->httpTimeout = $httpTimeout;
        $this->httpDelay = $httpDelay;
        $this->sslVerifyBehavior = $sslVerifyBehavior;
        $this->sslVerifyPath = $sslVerifyPath;
        $this->httpAuthUsername = $httpAuthUsername;
        $this->httpAuthPassword = $httpAuthPassword;
        $this->httpToHttps = $httpToHttps;
        $this->siteUrlProvider = $siteUrlProvider;
    }

    public function createInternalClient(array $options = []) : ClientInterface
    {
        return $this->createClient(\array_merge([
            RequestOptions::AUTH => $this->getAuthOption(),
            'handler' => $this->createDefaultStack(\true)
        ], $options));
    }

    public function createClient(array $options = []) : ClientInterface
    {
        $headers = [
            'User-Agent' => \sprintf('%s %s', HttpUtil::userAgent(), GuzzleUtils::defaultUserAgent())
        ];
        if (isset($options[RequestOptions::HEADERS])) {
            $headers = \array_merge($headers, $options[RequestOptions::HEADERS]);
            unset($options[RequestOptions::HEADERS]);
        }

        return new Client(\array_merge([
            RequestOptions::CONNECT_TIMEOUT => $this->httpTimeout->value(),
            RequestOptions::TIMEOUT => $this->httpTimeout->value(),
            RequestOptions::DELAY => $this->httpDelay->value(),
            RequestOptions::VERIFY => $this->getSslVerifyOption(),
            RequestOptions::HEADERS => $headers,
            'handler' => $this->createDefaultStack()
        ], $options));
    }

    public function createDefaultStack(bool $forInternal = \false) : HandlerStack
    {
        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory([
            'retry_on_timeout' => \true
        ]), 'retry');
        if (!$forInternal) {
            return $stack;
        }
        $replacements = $this->crawlerBodyReplacements();
        if (\count($replacements)) {
            $stack->push(RewriteResponseBodyMiddleware::factory([
                'replacements' => $replacements
            ]), 'body_replacements');
        }
        if ($this->httpToHttps->value()) {
            $stack->push(HttpsToHttpMiddleware::factory(), 'force_http');
        }

        return $stack;
    }

    private function crawlerBodyReplacements() : array
    {
        $replacements = [];
        /**
         * Filters the list of live URLs to replace with the local (dynamic)
         * site URL in the response body while crawling the site, in order to
         * allow these to be processed by the plugin.
         *
         * Passing an empty array to this hook will disable this functionality.
         *
         * @since 1.7.0
         *
         * @param string[] $replaceLiveUrls Array of live site URLs.
         */
        $replaceLiveUrls = \apply_filters('staatic_replace_live_urls', []);
        if (\count($replaceLiveUrls)) {
            $siteUrl = (string) ($this->siteUrlProvider)();
            foreach ($replaceLiveUrls as $liveUrl) {
                $replacements[\rtrim($liveUrl, '/') . '/'] = $siteUrl;
            }
        }
        /**
         * Filters the list of crawler response body replacements to apply.
         *
         * Passing an empty array to this hook will disable this functionality.
         *
         * @since 1.7.0
         *
         * @param array $replacements An array of body replacements.
         */
        $replacements = \apply_filters('staatic_crawler_body_replacements', $replacements);

        return $replacements;
    }

    public function createSymfonyClient() : HttpClientInterface
    {
        $sslVerifyBehavior = $this->sslVerifyBehavior->value();
        $verifySsl = $sslVerifyBehavior !== SslVerifyBehaviorSetting::VALUE_DISABLED;
        $options = (new HttpOptions())->setTimeout((float) $this->httpTimeout->value())->verifyHost(
            $verifySsl
        )->verifyPeer(
            $verifySsl
        )->setHeaders(
            [
            'User-Agent' => \sprintf('%s %s', HttpUtil::userAgent(), 'Symfony')
        ]
        );
        if ($sslVerifyBehavior === SslVerifyBehaviorSetting::VALUE_PATH) {
            if ($sslVerifyPath = \realpath($this->sslVerifyPath->value())) {
                $options->setCaFile($sslVerifyPath);
            }
        }
        $httpClient = HttpClient::create($options->toArray(), (int) $this->httpConcurrency->value());

        return new RetryableHttpClient($httpClient);
    }

    private function getSslVerifyOption()
    {
        $behavior = $this->sslVerifyBehavior->value();
        if ($behavior === SslVerifyBehaviorSetting::VALUE_PATH) {
            $path = $this->sslVerifyPath->value();

            return \realpath($path) ?: \true;
        } elseif ($behavior === SslVerifyBehaviorSetting::VALUE_DISABLED) {
            return \false;
        } else {
            return \true;
        }
    }

    private function getAuthOption() : ?array
    {
        $username = $this->httpAuthUsername->value();
        $password = $this->httpAuthPassword->value();
        if ($username || $password) {
            return [$username, $password];
        } else {
            return null;
        }
    }
}
