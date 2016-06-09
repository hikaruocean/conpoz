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
        'Conpoz\\Component\\' => APP_PATH . '/component/',
        'Conpoz\\Lib\\' => CORE_PATH . '/lib/',
        ),
    'db' => array(
        'adapter' => 'mysql',
        'dbname' => 'hikaru',
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'qeksnopre'
        )
    );