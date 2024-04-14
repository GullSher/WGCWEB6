<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait PublicTrait
{
    /**
     * @return static
     */
    public final function public()
    {
        $this->definition->setPublic(\true);
        return $this;
    }
    /**
     * @return static
     */
    public final function private()
    {
        $this->definition->setPublic(\false);
        return $this;
    }
}
