<?php

namespace Staatic\Crawler\UrlNormalizer;

use Staatic\Vendor\GuzzleHttp\Psr7\UriNormalizer;
use Staatic\Vendor\Psr\Http\Message\UriInterface;
final class BasicUrlNormalizer implements UrlNormalizerInterface
{
    /**
     * @var bool
     */
    private $removeFragment = \true;
    /**
     * @var bool
     */
    private $removeQuery = \true;
    public function __construct(bool $removeFragment = \true, bool $removeQuery = \true)
    {
        $this->removeFragment = $removeFragment;
        $this->removeQuery = $removeQuery;
    }
    /**
     * @param UriInterface $url
     */
    public function normalize($url) : UriInterface
    {
        if ($this->removeFragment && $url->getFragment()) {
            $url = $url->withFragment('');
        }
        if ($this->removeQuery && $url->getQuery()) {
            $url = $url->withQuery('');
        }
        $normalizations = UriNormalizer::PRESERVING_NORMALIZATIONS | UriNormalizer::REMOVE_DUPLICATE_SLASHES | UriNormalizer::SORT_QUERY_PARAMETERS;
        return UriNormalizer::normalize($url, $normalizations);
    }
}
