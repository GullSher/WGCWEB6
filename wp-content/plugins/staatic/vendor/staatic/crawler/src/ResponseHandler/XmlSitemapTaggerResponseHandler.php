<?php

namespace Staatic\Crawler\ResponseHandler;

use Staatic\Crawler\CrawlerInterface;
use Staatic\Crawler\CrawlUrl;
class XmlSitemapTaggerResponseHandler extends AbstractResponseHandler
{
    private const SITEMAP_XML_TAG = CrawlerInterface::TAG_SITEMAP_XML;
    private const SITEMAP_XML_NAMES = ['sitemap.xml', 'sitemaps.xml', 'sitemap_index.xml', 'wp-sitemap.xml'];
    /**
     * @param CrawlUrl $crawlUrl
     */
    public function handle($crawlUrl) : CrawlUrl
    {
        if (\in_array(\basename($crawlUrl->url()->getPath()), self::SITEMAP_XML_NAMES, \true)) {
            $crawlUrl = $crawlUrl->withTags(\array_merge($crawlUrl->tags(), [self::SITEMAP_XML_TAG]));
        }
        return parent::handle($crawlUrl);
    }
}
