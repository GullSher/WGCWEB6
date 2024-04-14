<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Dumper;

interface DumperInterface
{
    /**
     * @param mixed[] $options
     * @return string|mixed[]
     */
    public function dump($options = []);
}
