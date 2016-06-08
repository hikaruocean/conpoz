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
        'Conpoz\\Lib\\' => CORE_PATH . '/lib/',
        ),
    );