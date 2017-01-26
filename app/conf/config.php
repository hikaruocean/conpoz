<?php 
return array(
    'route' => array(
        'defaultController' => 'Index',
        'defaultAction' => 'index',
        '404Controller' => 'Error',
        '404Action' => 'http404',
        ),
    'autoloadNamespace' => array(
        'Conpoz\\App\\Controller\\' => APP_PATH . '/controller/',
        'Conpoz\\App\\Task\\' => APP_PATH . '/task/',
        'Conpoz\\App\\Model\\' => APP_PATH . '/model/',
        'Conpoz\\App\\Lib\\' => APP_PATH . '/lib/',
        'Conpoz\\Core\\Lib\\' => CORE_PATH . '/lib/',
        ),
    'db' => array(
        'adapter' => 'mysql',
        'dbname' => 'hikaru',
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => 'hikaru'
        ),
    'mem' => array(
        'host' => '127.0.0.1',
        'port' => 11211,
        ),
    'ACL' => array(
        'publicRole' => 'guest',
        'publicResources' => array(
            array('Index', '*'),
            array('Error', '*'),
            ),
        ),
    );