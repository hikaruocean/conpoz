<?php 
return array(
    'route' => array(
        'defaultController' => 'Index',
        'defaultAction' => 'index',
        '404Controller' => 'Error',
        '404Action' => 'http404',
        ),
    'autoloadNamespace' => array(
        'Conpoz\\Controller\\' => APP_PATH . '/controller/',
        'Conpoz\\Model\\' => APP_PATH . '/model/',
        'Conpoz\\Lib\\' => CORE_PATH . '/lib/',
        ),
    'db' => array(
        'adapter' => 'mysql',
        'dbname' => 'hikaru',
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => 'hikaru'
        )
    );