<?php

namespace Realejo\View\Helper;

use Realejo\View\Helper\ApplicationConfig;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;

class ApplicationConfigFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return ApplicationConfig
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ApplicationConfig($container->get('config'));
    }
}
