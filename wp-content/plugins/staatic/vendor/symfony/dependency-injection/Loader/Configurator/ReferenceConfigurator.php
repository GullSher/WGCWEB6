<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Loader\Configurator;

use Staatic\Vendor\Symfony\Component\DependencyInjection\ContainerInterface;
class ReferenceConfigurator extends AbstractConfigurator
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var int
     */
    protected $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    /**
     * @return static
     */
    public final function ignoreOnInvalid()
    {
        $this->invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
        return $this;
    }
    /**
     * @return static
     */
    public final function nullOnInvalid()
    {
        $this->invalidBehavior = ContainerInterface::NULL_ON_INVALID_REFERENCE;
        return $this;
    }
    /**
     * @return static
     */
    public final function ignoreOnUninitialized()
    {
        $this->invalidBehavior = ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE;
        return $this;
    }
    public function __toString() : string
    {
        return $this->id;
    }
}
