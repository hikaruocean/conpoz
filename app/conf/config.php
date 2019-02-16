<?php
$config = new \stdClass();
$config->route = array(
    'defaultController' => 'Index',
    'defaultAction' => 'index',
    '404Controller' => 'Error',
    '404Action' => 'http404',
);
$config->autoloadNamespace = array(
    'Conpoz\\App\\' => APP_PATH . '/',
    'Conpoz\\App\\Controller\\' => APP_PATH . '/controller/',
    'Conpoz\\App\\Task\\' => APP_PATH . '/task/',
    'Conpoz\\App\\Model\\' => APP_PATH . '/model/',
    'Conpoz\\App\\Lib\\' => APP_PATH . '/lib/',
    'Conpoz\\App\\Middleware\\' => APP_PATH . '/middleware/',
    'Conpoz\\Core\\Lib\\' => CORE_PATH . '/lib/',
);
$config->db = array(
    'master' => array(
        'adapter' => 'mysql',
        'dbname' => 'hikaru',
        'host' => '127.0.0.1',
        'port' => '3306',
        'username' => 'root',
        'password' => 'qeksnopre'
    ),
    'slave' => array(
        array(
            'adapter' => 'mysql',
            'dbname' => 'hikaru',
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => 'qeksnopre'
        ),
        array(
            'adapter' => 'mysql',
            'dbname' => 'hikaru',
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => 'qeksnopre'
        ),
    )
);
$config->mem = array(
    'host' => '127.0.0.1',
    'port' => 11211,
);
$config->ACL = array(
    'publicRole' => 'guest',
    'publicResources' => array(
        array('Index', '*'),
        array('Error', '*'),
    ),
);
$config->middlewareGroup = array(
    'Cors',
    'M1',
    'M2',
);
$config->middlewareGroup2 = array(
    'Cors',
    'M1',
    'M4',
    'M2',
);
$config->middlewareBind = array(
    '*' => 'M4',
    'Index' => array(
        '*' => $config->middlewareGroup,
        'update' => 'M3',
    ),
    'Member' => array(
        '*' => $config->middlewareGroup2,
        'needLogin' => 'M1',
    )
);
return $config;
