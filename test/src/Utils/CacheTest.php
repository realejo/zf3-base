<?php

namespace RealejoTest\Utils;

/**
 * CacheTest test case.
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */

use Realejo\Cache\CacheService;
use RealejoTest\BaseTestCase;

class CacheTest extends BaseTestCase
{
    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->cacheService = new CacheService();
        $this->cacheService->setCacheDir($this->getDataDir() . '/cache');

        $this->clearApplicationData();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        parent::tearDown();

        // Remove as pastas criadas
        $this->clearApplicationData();
    }

    /**
     * getCachePath sem nome da pasta
     */
    public function testGetCacheRoot()
    {
        // Recupera a pasta aonde será salva as informações
        $path = $this->cacheService->getCacheRoot();

        // Verifica se tere o retorno correto
        $this->assertNotNull($path);
        $this->assertEquals(realpath(TEST_DATA . '/cache'), $path);
        $this->assertTrue(file_exists($path));
        $this->assertTrue(is_dir($path));
        $this->assertTrue(is_writable($path));
    }

    /**
     * getCachePath sem nome da pasta
     */
    public function testGetCachePath()
    {
        // Verifica se todas as opções são iguais
        $this->assertEquals($this->cacheService->getCacheRoot(), $this->cacheService->getCachePath(null));
        $this->assertEquals($this->cacheService->getCacheRoot(), $this->cacheService->getCachePath(''));
        $this->assertEquals($this->cacheService->getCacheRoot(), $this->cacheService->getCachePath());

        // Cria ou recupera a pasta album
        $path = $this->cacheService->getCachePath('Album');

        // Verifica se foi criada corretamente a pasta
        $this->assertNotNull($path);
        $this->assertEquals(realpath(TEST_DATA . '/cache/album'), $path);
        $this->assertNotEquals(realpath(TEST_DATA . '/cache/Album'), $path);
        $this->assertTrue(file_exists($path));
        $this->assertTrue(is_dir($path));
        $this->assertTrue(is_writable($path));

        // Apaga a pasta
        $this->rrmdir($path);

        // Verifica se a pasta foi apagada
        $this->assertFalse(file_exists($path));

        // Cria ou recupera a pasta album
        $path = $this->cacheService->getCachePath('album');

        // Verifica se foi criada corretamente a pasta
        $this->assertNotNull($path);
        $this->assertEquals(realpath(TEST_DATA . '/cache/album'), $path);
        $this->assertNotEquals(realpath(TEST_DATA . '/cache/Album'), $path);
        $this->assertTrue(file_exists($path), 'Verifica se a pasta album existe');
        $this->assertTrue(is_dir($path), 'Verifica se a pasta album é uma pasta');
        $this->assertTrue(is_writable($path), 'Verifica se a pasta album tem permissão de escrita');

        // Apaga a pasta
        $this->rrmdir($path);

        // Verifica se a pasta foi apagada
        $this->assertFalse(file_exists($path));

        // Cria ou recupera a pasta
        $path = $this->cacheService->getCachePath('album_Teste');

        // Verifica se foi criada corretamente a pasta
        $this->assertNotNull($path);
        $this->assertEquals(realpath(TEST_DATA . '/cache/album/teste'), $path);
        $this->assertNotEquals(realpath(TEST_DATA . '/cache/Album/Teste'), $path);
        $this->assertTrue(file_exists($path), 'Verifica se a pasta album_Teste existe');
        $this->assertTrue(is_dir($path), 'Verifica se a pasta album_Teste é uma pasta');
        $this->assertTrue(is_writable($path), 'Verifica se a pasta album_Teste tem permissão de escrita');

        // Apaga a pasta
        $this->rrmdir($path);

        // Verifica se a pasta foi apagada
        $this->assertFalse(file_exists($path), 'Verifica se a pasta album_Teste foi apagada');

        // Cria ou recupera a pasta
        $path = $this->cacheService->getCachePath('album/Teste');

        // Verifica se foi criada corretamente a pasta
        $this->assertNotNull($path, 'Teste se o album/Teste foi criado');
        $this->assertEquals(realpath(TEST_DATA . '/cache/album/teste'), $path);
        $this->assertNotEquals(realpath(TEST_DATA . '/cache/Album/Teste'), $path);
        $this->assertTrue(file_exists($path), 'Verifica se a pasta album/Teste existe');
        $this->assertTrue(is_dir($path), 'Verifica se a pasta album/Teste é uma pasta');
        $this->assertTrue(is_writable($path), 'Verifica se a pasta album/Teste tem permissão de escrita');
    }

    /**
     * getFrontend com nome da class
     */
    public function testgetFrontendComClass()
    {
        $cache = $this->cacheService->getFrontend('Album');
        $this->assertInstanceOf('Zend\Cache\Storage\Adapter\Filesystem', $cache);
    }

    /**
     * getFrontend sem nome da class
     */
    public function testgetFrontendSemClass()
    {
        $cache = $this->cacheService->getFrontend(null);
        $this->assertInstanceOf('Zend\Cache\Storage\Adapter\Filesystem', $cache);
    }
}
