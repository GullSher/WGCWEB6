<?php

namespace Staatic\Crawler\UrlTransformer;

use Staatic\Vendor\Psr\Http\Message\UriInterface;
final class SameUrlTransformer implements UrlTransformerInterface
{
    /**
     * @param UriInterface $url
     * @param UriInterface|null $foundOnUrl
     */
    public function transform($url, $foundOnUrl = null) : UrlTransformation
    {
        return new UrlTransformation($url);
    }
}
