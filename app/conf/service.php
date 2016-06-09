<?php 
$bag = new \Conpoz\Lib\Util\Container();

$bag->config = $config;
$bag->dbquery = function() use (&$config) {
    $db = new \Conpoz\Lib\Db\DBQuery($config['db']);
    if (!$db->success()) {
    	throw new \Exception($db->error());
    }
    return $db;
};

return $bag;