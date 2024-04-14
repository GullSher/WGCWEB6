<?php

namespace Staatic\Vendor;

if (\PHP_VERSION_ID < 80000 && !\interface_exists('Stringable')) {
    interface Stringable
    {
        public function __toString();
    }
}
