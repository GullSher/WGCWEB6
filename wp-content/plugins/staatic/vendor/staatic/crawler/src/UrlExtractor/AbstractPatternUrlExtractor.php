<?php

namespace Staatic\Crawler\UrlExtractor;

use Closure;
use Generator;
use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use InvalidArgumentException;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Crawler\UriHelper;
use Staatic\Crawler\UrlTransformer\UrlTransformation;
abstract class AbstractPatternUrlExtractor implements UrlExtractorInterface, FilterableInterface, TransformableInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var \Closure|null
     */
    private $filterCallback;
    /**
     * @var \Closure|null
     */
    private $transformCallback;
    /**
     * @var string
     */
    protected $content;
    /**
     * @var UriInterface
     */
    protected $baseUrl;
    /**
     * @var mixed[]
     */
    protected $pattern;
    public function __construct(?callable $filterCallback = null, ?callable $transformCallback = null)
    {
        $this->logger = new NullLogger();
        $this->setFilterCallback($filterCallback);
        $this->setTransformCallback($transformCallback);
    }
    /**
     * @param string $content
     * @param UriInterface $baseUrl
     */
    public function extract($content, $baseUrl) : Generator
    {
        $this->content = $content;
        $this->baseUrl = $baseUrl;
        foreach ($this->getPatterns() as $pattern) {
            $pattern = \is_array($pattern) ? $pattern : ['pattern' => $pattern];
            yield from $this->extractUsingPattern($pattern);
        }
        return $this->content;
    }
    protected abstract function getPatterns() : array;
    /**
     * @param mixed[] $pattern
     */
    protected function extractUsingPattern($pattern) : Generator
    {
        $this->pattern = $pattern;
        $extractedUrls = [];
        $this->content = \preg_replace_callback($this->pattern['pattern'], function ($match) use(&$extractedUrls) {
            return $this->handleMatch($extractedUrls, $match[0], $match[1]);
        }, $this->content);
        foreach ($extractedUrls as $resolvedUrl => $transformedUrl) {
            (yield $resolvedUrl => $transformedUrl);
        }
    }
    /**
     * @param mixed[] $extractedUrls
     * @param string $fullMatch
     * @param string $matchedUrl
     */
    protected function handleMatch(&$extractedUrls, $fullMatch, $matchedUrl) : string
    {
        $decodedUrl = $this->decode($matchedUrl);
        if (!UriHelper::isReplaceableUrl($decodedUrl)) {
            return $fullMatch;
        }
        try {
            $resolvedUrl = UriResolver::resolve($this->baseUrl, new Uri($decodedUrl));
        } catch (InvalidArgumentException $e) {
            return $fullMatch;
        }
        if ($this->filterCallback && ($this->filterCallback)($resolvedUrl)) {
            return \str_replace($matchedUrl, $this->encode((string) $resolvedUrl), $fullMatch);
        }
        $urlTransformation = $this->transformCallback ? ($this->transformCallback)($resolvedUrl, $this->baseUrl) : new UrlTransformation($resolvedUrl);
        $extractedUrls[(string) $resolvedUrl] = $urlTransformation->transformedUrl();
        return \str_replace($matchedUrl, $this->encode((string) $urlTransformation->effectiveUrl()), $fullMatch);
    }
    private function encode(string $content) : string
    {
        return isset($this->pattern['encode']) ? $this->pattern['encode']($content) : $content;
    }
    private function decode(string $content) : string
    {
        return isset($this->pattern['decode']) ? $this->pattern['decode']($content) : $content;
    }
    /**
     * @param callable|null $callback
     */
    public function setFilterCallback($callback) : void
    {
        $this->filterCallback = $callback ? Closure::fromCallable($callback) : null;
    }
    /**
     * @param callable|null $callback
     */
    public function setTransformCallback($callback) : void
    {
        $this->transformCallback = $callback ? Closure::fromCallable($callback) : null;
    }
}
