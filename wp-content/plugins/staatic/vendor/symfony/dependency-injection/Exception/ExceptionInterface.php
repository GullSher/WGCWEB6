<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\Exception;

use Throwable;
use Staatic\Vendor\Psr\Container\ContainerExceptionInterface;
interface ExceptionInterface extends ContainerExceptionInterface, Throwable
{
}
