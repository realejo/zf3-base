<?php
/**
 * Gerenciador de cache utilizado pelo App_Model
 *
 * Ele cria automaticamente a pasta de cache, dentro de data/cache, baseado no nome da classe
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */

namespace Realejo\Cache;

use Zend\Cache\StorageFactory;

class CacheService
{
    /**
     * @var \Zend\Cache\Storage\Adapter\Filesystem
     */
    protected $cache;

    protected $cacheDir;

    /**
     * Configura o cache
     *
     * @param string $class
     * @return \Zend\Cache\Storage\Adapter\Filesystem
     */
    public function getFrontend($class = '')
    {
        $cacheService = new self();

        $path = $this->getCachePath($class);

        if (!empty($path)) {
            // Configura o cache
            $cacheService->cache = StorageFactory::factory([
                'adapter' => [
                    'name' => 'filesystem',
                    'options' => [
                        'cache_dir' => $path,
                        'namespace' => $this->getNamespace($class),
                        'dir_level' => 0,
                    ],
                ],
                'plugins' => [
                    // Don't throw exceptions on cache errors
                    'exception_handler' => [
                        'throw_exceptions' => false
                    ],
                    'Serializer'
                ],
                'options' => [
                    'ttl' => 86400
                ]
            ]);
        }

        return $cacheService->cache;
    }

    /**
     * Retorna o padrão do namespace a ser usado no cache
     *
     * @param string $class
     *
     * @return string
     */
    public function getNamespace($class)
    {
        return str_replace(['_', '\\', '/'], '.', strtolower($class));
    }

    /**
     * Apaga o cache de consultas do model
     */
    public function clean()
    {
        // Apaga o cache
        $this->getFrontend()->flush();
    }

    /**
     * Retorna a pasta raiz de todos os caches
     * @return string
     */
    public function getCacheRoot()
    {
        $cacheRoot = $this->cacheDir;

        // Verifica se a pasta de cache existe
        if ($cacheRoot === null) {
            if (defined('APPLICATION_DATA') === false) {
                throw new \RuntimeException('A pasta raiz do data não está definido em APPLICATION_DATA');
            }
            $cacheRoot = APPLICATION_DATA  . '/cache';
        }

        // Verifica se a pasta do cache existe
        if (!file_exists($cacheRoot)) {
            $oldUMask = umask(0);
            mkdir($cacheRoot, 0777, true);
            umask($oldUMask);
        }

        // retorna a pasta raiz do cache
        return realpath($cacheRoot);
    }

    /**
     * Retorna a pasta de cache para o model baseado no nome da classe
     * Se a pasta não existir ela será criada
     *
     * @param string $class Nome da classe a ser usada
     *
     * @return string
     */
    public function getCachePath($class = '')
    {
        // Define a pasta de cache
        $cachePath = $this->getCacheRoot() . '/' . str_replace(['_', '\\'], '/', strtolower($class));

        // Verifica se a pasta do cache existe
        if (!file_exists($cachePath)) {
            $oldumask = umask(0);
            mkdir($cachePath, 0777, true);
            umask($oldumask);
        }

        // Retorna a pasta de cache
        return realpath($cachePath);
    }

    /**
     * Ignora o backend e apaga os arquivos do cache. inclui as sub pastas.
     * Serão removidos apenas os arquivos de cache e não as pastas
     *
     * @param string $path
     */
    public function completeCleanUp($path)
    {
        if (is_dir($path)) {
            $results = scandir($path);
            foreach ($results as $result) {
                if ($result === '.' or $result === '..') {
                    continue;
                }

                if (is_file($path . '/' . $result)) {
                    unlink($path . '/' . $result);
                }

                if (is_dir($path . '/' . $result)) {
                    $this->completeCleanUp($path . '/' . $result);
                }
            }
        }
    }

    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }
}
