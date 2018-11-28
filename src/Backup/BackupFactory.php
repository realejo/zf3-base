<?php

namespace Realejo\Backup;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\FactoryInterface;

class BackupFactory implements FactoryInterface
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
        if (!isset($config['realejo']['backup']['dump_dir'])) {
            throw new ServiceNotCreatedException('dump_dir not defined');
        }
        $dumpPath = realpath($config['realejo']['backup']['dump_dir']);
        if ($dumpPath === false) {
            throw new ServiceNotCreatedException($config['realejo']['backup']['dump_dir'] . ' not exists.');
        }

        $cacheService = new BackupService();
        $cacheService->setDumpPath($dumpPath);

        if (isset($config['db'])) {
            $cacheService->setConfig($config['db']);
        }

        return $cacheService;
    }
}
