<?php

declare (strict_types=1);
namespace Staatic\Vendor\DOMWrap\Traits;

use DOMDocument;
use Staatic\Vendor\DOMWrap\NodeList;
trait CommonTrait
{
    public abstract function collection() : NodeList;
    public abstract function document() : ?DOMDocument;
    /**
     * @param NodeList $nodeList
     */
    public abstract function result($nodeList);
    public function isRemoved() : bool
    {
        return !isset($this->nodeType);
    }
}
