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
define('APPLICATION_DATA', TEST_ROOT . '/_files/data');

/**
 * Setup autoloading
 */

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $loader = include __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ .'/../../../vendor/autoload.php')) {
    $loader = include __DIR__ .'/../../../vendor/autoload.php';
} elseif (file_exists(__DIR__ .'/../../../autoload.php')) {
    $loader = include __DIR__ .'/../../../autoload.php';
} else {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

// Carrega os namespaces para teste
$loader->addPsr4("RealejoTest\\", __DIR__ );
