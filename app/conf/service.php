<?php 
$bag = new \Conpoz\Lib\Util\Container();

$bag->config = $config;
$bag->dbquery = function () use (&$config) {
    $db = new \Conpoz\Lib\Db\DBQuery($config['db']);
    if (!$db->success()) {
    	throw new \Exception($db->error());
    }
    return $db;
};

$bag->req = function () {
    $req = new \Conpoz\Lib\Util\Request();
    return $req;
};

$bag->sess = function () {
    session_set_cookie_params(0, '/', '.conpoz.lo');
    return new \Conpoz\Lib\Util\Session();
};

$bag->imgMng = function () {
    return new \Conpoz\Lib\Util\ImageManager();
};

$bag->net = function () {
    return new \Conpoz\Lib\Util\Network();
};

$bag->tool = function () {
    return new \Conpoz\Lib\Util\Tool();
};

return $bag;