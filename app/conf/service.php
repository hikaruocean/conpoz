<?php 
$bag = new \Conpoz\Lib\Util\Container();

$bag->dbquery = function() use (&$config) {
    return new \Conpoz\Lib\Db\DBQuery($config['db']);
};

$bag->view = function() {
    return new \Conpoz\Lib\Mvc\View();
}
return $bag;