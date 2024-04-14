<?php

namespace Staatic\Crawler\UrlExtractor;

use Closure;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Generator;
use Staatic\Vendor\GuzzleHttp\Psr7\Uri;
use Staatic\Vendor\GuzzleHttp\Psr7\UriResolver;
use InvalidArgumentException;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Crawler\DomParser\DomParserInterface;
use Staatic\Crawler\DomParser\DomWrapDomParser;
use Staatic\Crawler\UriHelper;
use Staatic\Crawler\UrlExtractor\Mapping\HtmlUrlExtractorMapping;
use Staatic\Crawler\UrlTransformer\UrlTransformation;
final class HtmlUrlExtractor implements UrlExtractorInterface, FilterableInterface, TransformableInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var DomParserInterface
     */
    private $domParser;
    /**
     * @var UrlExtractorInterface
     */
    private $cssExtractor;
    /**
     * @var mixed[]
     */
    private $mapping;
    /**
     * @var bool
     */
    private $contextEnabled;
    /**
     * @var string
     */
    private $tagsExpression;
    /**
     * @var string
     */
    private $tagsSelector;
    /**
     * @var mixed[]
     */
    private $srcsetAttributes;
    /**
     * @var \Closure|null
     */
    private $filterCallback;
    /**
     * @var \Closure|null
     */
    private $transformCallback;
    public function __construct(DomParserInterface $domParser, $cssExtractor = null, $mapping = null, bool $contextEnabled = \false)
    {
        $this->logger = new NullLogger();
        $this->domParser = $domParser ?? new DomWrapDomParser();
        $this->cssExtractor = $cssExtractor ?? new CssUrlExtractor();
        $mapping = $mapping ?? new HtmlUrlExtractorMapping();
        $this->mapping = $mapping->mapping();
        $this->srcsetAttributes = $mapping->srcsetAttributes();
        $this->tagsExpression = '(//' . \implode(')|(//', \array_keys($this->mapping)) . ')';
        $this->tagsSelector = \implode(', ', \array_keys($this->mapping));
        $this->contextEnabled = $contextEnabled;
    }
    /**
     * @param string $content
     * @param UriInterface $baseUrl
     */
    public function extract($content, $baseUrl) : Generator
    {
        $document = $this->domParser->documentFromHtml($content);
        foreach ($this->findMappedElements($document) as $element) {
            $attributes = $this->mapping[$element->localName];
            yield from $this->handleElementAttributes($element, $attributes, $baseUrl);
        }
        foreach ($this->findStyleElements($document) as $element) {
            $elementTextContent = $this->domParser->getText($element);
            $elementTextContentBefore = $elementTextContent;
            $generator = $this->cssExtractor->extract($elementTextContent, $baseUrl);
            yield from $generator;
            $elementTextContent = $generator->getReturn();
            if ($elementTextContent !== $elementTextContentBefore) {
                $this->domParser->setText($element, $elementTextContent);
            }
        }
        $newContent = $this->domParser->getHtml($document);
        if (empty($newContent)) {
            $this->logger->warning("Unable to transform HTML");
        }
        return $newContent ?: $content;
    }
    private function findMappedElements($document)
    {
        if ($document instanceof DOMDocument) {
            return (new DOMXPath($document))->query($this->tagsExpression);
        } else {
            return $document->find($this->tagsSelector);
        }
    }
    private function findStyleElements($document)
    {
        if ($document instanceof DOMDocument) {
            return (new DOMXPath($document))->query('//style');
        } else {
            return $document->find('style');
        }
    }
    private function handleElementAttributes($element, array $attributes, UriInterface $baseUrl) : Generator
    {
        if ($element->hasAttribute('style')) {
            $attributeValue = $this->domParser->getAttribute($element, 'style');
            $attributeValueBefore = $attributeValue;
            $generator = $this->cssExtractor->extract($attributeValue, $baseUrl);
            yield from $generator;
            $attributeValue = $generator->getReturn();
            if ($attributeValue !== $attributeValueBefore) {
                $this->domParser->setAttribute($element, 'style', $attributeValue);
            }
        }
        foreach ($attributes as $attributeName) {
            if (!$element->hasAttribute($attributeName)) {
                continue;
            }
            $attributeValue = $this->domParser->getAttribute($element, $attributeName);
            $attributeValueBefore = $attributeValue;
            if (\in_array($attributeName, $this->srcsetAttributes)) {
                $extractedUrls = $this->extractUrlsFromSrcset($attributeValue);
            } else {
                $extractedUrls = [$attributeValue];
            }
            foreach ($extractedUrls as $extractedUrl) {
                $extractedUrl = \trim($extractedUrl);
                if (!UriHelper::isReplaceableUrl($extractedUrl)) {
                    continue;
                }
                $preserveEmptyFragment = substr_compare($extractedUrl, '#', -strlen('#')) === 0;
                try {
                    $resolvedUrl = UriResolver::resolve($baseUrl, new Uri($extractedUrl));
                } catch (InvalidArgumentException $e) {
                    $this->logger->warning("Encountered an unparsable URL: '{$extractedUrl}'");
                    continue;
                }
                if ($this->filterCallback && ($this->filterCallback)($resolvedUrl, $this->contextEnabled ? ['htmlTagName' => $element->tagName, 'htmlAttributeName' => $attributeName, 'htmlElement' => (string) $element] : [])) {
                    $attributeValue = \str_replace($extractedUrl, (string) $resolvedUrl . ($preserveEmptyFragment ? '#' : ''), $attributeValue);
                    continue;
                }
                $urlTransformation = $this->transformCallback ? ($this->transformCallback)($resolvedUrl, $baseUrl) : new UrlTransformation($resolvedUrl);
                (yield (string) $resolvedUrl => $urlTransformation->transformedUrl());
                $attributeValue = \str_replace($extractedUrl, (string) $urlTransformation->effectiveUrl() . ($preserveEmptyFragment ? '#' : ''), $attributeValue);
            }
            if ($attributeValue !== $attributeValueBefore) {
                $this->domParser->setAttribute($element, $attributeName, $attributeValue);
            }
        }
    }
    private function extractUrlsFromSrcset(string $srcset) : array
    {
        \preg_match_all('~([^\\s]+)\\s*(?:[\\d\\.]+[wx])?,*~m', $srcset, $matches);
        return $matches[1];
    }
    /**
     * @param callable|null $callback
     */
    public function setFilterCallback($callback) : void
    {
        $this->filterCallback = $callback ? Closure::fromCallable($callback) : null;
        if ($this->cssExtractor instanceof FilterableInterface) {
            $this->cssExtractor->setFilterCallback($callback);
        }
    }
    /**
     * @param callable|null $callback
     */
    public function setTransformCallback($callback) : void
    {
        $this->transformCallback = $callback ? Closure::fromCallable($callback) : null;
        if ($this->cssExtractor instanceof TransformableInterface) {
            $this->cssExtractor->setTransformCallback($callback);
        }
    }
}
