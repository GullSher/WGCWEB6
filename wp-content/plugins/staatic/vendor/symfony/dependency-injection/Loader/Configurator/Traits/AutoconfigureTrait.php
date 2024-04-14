<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

use Staatic\Vendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
trait AutoconfigureTrait
{
    /**
     * @param bool $autoconfigured
     * @return static
     */
    public final function autoconfigure($autoconfigured = \true)
    {
        $this->definition->setAutoconfigured($autoconfigured);
        return $this;
    }
}
