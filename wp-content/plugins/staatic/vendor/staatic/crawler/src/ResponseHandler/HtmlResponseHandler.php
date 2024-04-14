<?php

namespace Staatic\Crawler\ResponseHandler;

use Staatic\Crawler\DomParser\DomWrapDomParser;
use Staatic\Crawler\DomParser\Html5DomParser;
use Staatic\Crawler\DomParser\SimpleHtmlDomParser;
use Staatic\Vendor\GuzzleHttp\Psr7\Utils;
use Staatic\Vendor\Psr\Http\Message\ResponseInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Crawler\CrawlOptions;
use Staatic\Crawler\CrawlUrl;
use Staatic\Crawler\DomParser;
use Staatic\Crawler\ResponseUtil;
use Staatic\Crawler\UrlExtractor\HtmlUrlExtractor;
class HtmlResponseHandler extends AbstractResponseHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var HtmlUrlExtractor|null
     */
    private $extractor;
    public function __construct()
    {
        $this->logger = new NullLogger();
    }
    /**
     * @param CrawlUrl $crawlUrl
     */
    public function handle($crawlUrl) : CrawlUrl
    {
        if ($crawlUrl->response() && $this->isHtmlResponse($crawlUrl->response())) {
            return $this->handleHtmlResponse($crawlUrl);
        }
        return parent::handle($crawlUrl);
    }
    private function isHtmlResponse(ResponseInterface $response) : bool
    {
        return ResponseUtil::getMimeType($response) === 'text/html';
    }
    private function handleHtmlResponse(CrawlUrl $crawlUrl) : CrawlUrl
    {
        $readMaximumBytes = $this->crawler->crawlOptions()->maxResponseBodyInBytes();
        $responseBody = ResponseUtil::convertBodyToString($crawlUrl->response()->getBody(), $readMaximumBytes);
        $generator = $this->extractor()->extract($responseBody, $crawlUrl->url());
        $this->processExtractedUrls($crawlUrl, $generator);
        $responseBody = Utils::streamFor($generator->getReturn());
        return $crawlUrl->withResponse($crawlUrl->response()->withBody($responseBody));
    }
    private function extractor() : HtmlUrlExtractor
    {
        if (!$this->extractor) {
            switch ($this->crawler->crawlOptions()->domParser()) {
                case CrawlOptions::DOM_PARSER_DOM_WRAP:
                    $domParser = new DomWrapDomParser();
                    break;
                case CrawlOptions::DOM_PARSER_HTML5:
                    $domParser = new Html5DomParser();
                    break;
                case CrawlOptions::DOM_PARSER_SIMPLE_HTML:
                    $domParser = new SimpleHtmlDomParser();
                    break;
                default:
                    $domParser = new DomWrapDomParser();
                    break;
            }
            $crawlOptions = $this->crawler->crawlOptions();
            $this->extractor = new HtmlUrlExtractor($domParser, null, $crawlOptions->htmlUrlExtractorMapping(), $crawlOptions->urlContextEnabled());
            $this->extractor->setFilterCallback($this->urlFilterCallback());
            $this->extractor->setTransformCallback($this->urlTransformCallback());
        }
        return $this->extractor;
    }
}
