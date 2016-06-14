<?php 

try {
    define('APP_PATH', __DIR__ . '/../app');
    define('CORE_PATH', __DIR__ . '/../core');

    $config = require(APP_PATH . '/conf/config.php');
    require(CORE_PATH . '/include/autoLoader.php');
    require(__DIR__ . '/../vendor/autoload.php');
    $bag = require(APP_PATH . '/conf/service.php');
    $app = new \Conpoz\Lib\Mvc\App($bag);
    $app->run($config);
} catch (\Exception $e) {
    die($e->getMessage());
}