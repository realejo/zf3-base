<?php
/**
 * Realejo Lib Unit Test Bootstrap
 *
 * @category  TestUnit
 * @author    Realejo
 * @copyright Copyright (c) 2013 Realejo (http://realejo.com.br)
 */
error_reporting(E_ALL | E_STRICT);

define('APPLICATION_ENV', 'testing');
define('TEST_ROOT', __DIR__);
define('TEST_DATA', TEST_ROOT . '/assets/data');

/**
 * Setup autoloading
 */

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $loader = require __DIR__ . '/../vendor/autoload.php';
}

// Procura pelas configurações do Semaphore
if (isset($_SERVER['DATABASE_MYSQL_USERNAME'])) {
    // Define o banco de dados de testes
    \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter(new Zend\Db\Adapter\Adapter([
        'driver' => 'mysqli',
        'host'           => '127.0.0.1',
        'username'       => $_SERVER['DATABASE_MYSQL_USERNAME'],
        'password'       => $_SERVER['DATABASE_MYSQL_PASSWORD'],
        'dbname'         => 'test',
        'options' => [
            'buffer_results' => true,
        ],
    ]));

// Procura pelas configurações do Codeship
} elseif (isset($_SERVER['MYSQL_USER'])) {
    // Define o banco de dados de testes
    \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter(new Zend\Db\Adapter\Adapter([
        'driver' => 'mysqli',
        'host'           => '127.0.0.1',
        'username'       => $_SERVER['MYSQL_USER'],
        'password'       => $_SERVER['MYSQL_PASSWORD'],
        'dbname'         => 'test',
        'options' => [
            'buffer_results' => true,
        ],
    ]));
} else {
    // Define o banco de dados de testes
    $config = (file_exists(__DIR__. '/configs/db.php')) ? __DIR__.'/configs/db.php' : __DIR__.'/configs/db.php.dist';
    \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter(new Zend\Db\Adapter\Adapter(require $config));
}
