<?php 
$bag = new \Conpoz\Lib\Util\Container();

$bag->dbquery = function() use (&$config) {
    $db = new \Conpoz\Lib\Db\DBQuery($config['db']);
    if (!$db->success()) {
    	throw new \Exception($db->error());
    }
    return $db;
};

$bag->view = function() {
    return new \Conpoz\Lib\Mvc\View();
};
return $bag;