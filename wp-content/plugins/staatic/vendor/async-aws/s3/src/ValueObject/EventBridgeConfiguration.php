<?php

namespace Staatic\Vendor\AsyncAws\S3\ValueObject;

use DOMElement;
use DOMDocument;
final class EventBridgeConfiguration
{
    public static function create($input) : self
    {
        return $input instanceof self ? $input : new self();
    }
    public function requestBody(DOMElement $node, DOMDocument $document) : void
    {
    }
}
