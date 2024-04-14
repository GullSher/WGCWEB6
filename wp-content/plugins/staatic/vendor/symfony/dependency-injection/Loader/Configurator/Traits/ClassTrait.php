<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait ClassTrait
{
    /**
     * @param string|null $class
     * @return static
     */
    public final function class($class)
    {
        $this->definition->setClass($class);
        return $this;
    }
}
