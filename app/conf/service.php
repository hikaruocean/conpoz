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

$bag->req = function() {
    $req = new \Conpoz\Lib\Util\Request();
    return $req;
};

$bag->imageLoader = function () {
    // create an image manager instance with favored driver
    return new \Intervention\Image\ImageManager(array('driver' => 'gd'));
};

return $bag;