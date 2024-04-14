<?php

declare (strict_types=1);
namespace Staatic\Vendor\ZipStream;

class DeflateStream extends Stream
{
    public function __construct($stream)
    {
        parent::__construct($stream);
        \trigger_error('Class ' . __CLASS__ . ' is deprecated, delation will be handled internally instead', \E_USER_DEPRECATED);
    }
    public function removeDeflateFilter() : void
    {
        \trigger_error('Method ' . __METHOD__ . ' is deprecated', \E_USER_DEPRECATED);
    }
    /**
     * @param \Staatic\Vendor\ZipStream\Option\File $options
     */
    public function addDeflateFilter($options) : void
    {
        \trigger_error('Method ' . __METHOD__ . ' is deprecated', \E_USER_DEPRECATED);
    }
}
