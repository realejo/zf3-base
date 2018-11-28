<?php

namespace Realejo\View\Helper;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ApplicationConfigFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     *
     * @return ApplicationConfig
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ApplicationConfig($container->get('config'));
    }
}
