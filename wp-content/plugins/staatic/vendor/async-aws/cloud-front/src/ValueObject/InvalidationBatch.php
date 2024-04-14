<?php

namespace Staatic\Vendor\AsyncAws\CloudFront\ValueObject;

use DOMElement;
use DOMDocument;
use Staatic\Vendor\AsyncAws\Core\Exception\InvalidArgument;
final class InvalidationBatch
{
    private $paths;
    private $callerReference;
    public function __construct(array $input)
    {
        $this->paths = isset($input['Paths']) ? Paths::create($input['Paths']) : null;
        $this->callerReference = $input['CallerReference'] ?? null;
    }
    public static function create($input) : self
    {
        return $input instanceof self ? $input : new self($input);
    }
    public function getCallerReference() : string
    {
        return $this->callerReference;
    }
    public function getPaths() : Paths
    {
        return $this->paths;
    }
    public function requestBody(DOMElement $node, DOMDocument $document) : void
    {
        if (null === ($v = $this->paths)) {
            throw new InvalidArgument(\sprintf('Missing parameter "Paths" for "%s". The value cannot be null.', __CLASS__));
        }
        $node->appendChild($child = $document->createElement('Paths'));
        $v->requestBody($child, $document);
        if (null === ($v = $this->callerReference)) {
            throw new InvalidArgument(\sprintf('Missing parameter "CallerReference" for "%s". The value cannot be null.', __CLASS__));
        }
        $node->appendChild($document->createElement('CallerReference', $v));
    }
}
