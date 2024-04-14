<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Rfc4122;

trait NilTrait
{
    public abstract function getBytes() : string;
    public function isNil() : bool
    {
        return $this->getBytes() === "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
    }
}
