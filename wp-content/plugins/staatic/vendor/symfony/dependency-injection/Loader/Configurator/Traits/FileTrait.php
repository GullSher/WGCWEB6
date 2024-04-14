<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait FileTrait
{
    /**
     * @param string $file
     * @return static
     */
    public final function file($file)
    {
        $this->definition->setFile($file);
        return $this;
    }
}
