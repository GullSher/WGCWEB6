<?php

namespace Staatic\Vendor\AsyncAws\S3\ValueObject;

use DOMElement;
use DOMDocument;
use Staatic\Vendor\AsyncAws\Core\Exception\InvalidArgument;
final class CORSConfiguration
{
    private $corsRules;
    public function __construct(array $input)
    {
        $this->corsRules = isset($input['CORSRules']) ? \array_map([CORSRule::class, 'create'], $input['CORSRules']) : null;
    }
    public static function create($input) : self
    {
        return $input instanceof self ? $input : new self($input);
    }
    public function getCorsRules() : array
    {
        return $this->corsRules ?? [];
    }
    public function requestBody(DOMElement $node, DOMDocument $document) : void
    {
        if (null === ($v = $this->corsRules)) {
            throw new InvalidArgument(\sprintf('Missing parameter "CORSRules" for "%s". The value cannot be null.', __CLASS__));
        }
        foreach ($v as $item) {
            $node->appendChild($child = $document->createElement('CORSRule'));
            $item->requestBody($child, $document);
        }
    }
}
