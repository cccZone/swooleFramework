<?php


namespace Kernel\Core\Di;

use DI\Container as PHP_DI;
use DI\Definition\Source\DefinitionSource;
use DI\Proxy\ProxyFactory;
use Psr\Container\ContainerInterface;

class Container extends PHP_DI implements ContainerInterface
{
        public function __construct(DefinitionSource $definitionSource, ProxyFactory $proxyFactory, ContainerInterface $wrapperContainer = null)
        {
                parent::__construct($definitionSource, $proxyFactory, $wrapperContainer);
        }
}