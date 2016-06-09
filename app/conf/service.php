<?php 
$service = new \Conpoz\Lib\Util\Container();

$service->dbquery = function() use (&$config) {
    return new \Conpoz\Lib\Db\DBQuery($config['db']);
};
return $service;