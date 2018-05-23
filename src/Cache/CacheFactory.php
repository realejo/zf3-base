<?php

namespace Realejo\Cache;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\FactoryInterface;

class CacheFactory implements FactoryInterface
{

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $cacheDir = isset($config['realejo']['cache']['cache_dir']) ?
            realpath($config['realejo']['cache']['cache_dir']) : false;
        if ($cacheDir === false) {
            throw new ServiceNotCreatedException('cache_dir not defined');
        }

        $cacheService = new CacheService();
        $cacheService->setCacheDir($cacheDir);

        return $cacheService;
    }
}

