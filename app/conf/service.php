<?php 
use Conpoz\Core\Lib\Db\DBQuery;

$bag = new \Conpoz\Core\Lib\Util\Container();

$bag->config = $config;
$bag->dbquery = function () use ($config) {
    $db = new \Conpoz\Core\Lib\Db\DBQuery($config->db);
    $db->persistent = true;
    $db->emulatePrepare = true;
    $db->setSqlErrorHandler(function ($rh) {
        throw new \Conpoz\Core\Lib\Db\DBQuery\Exception(json_encode($rh->error()));
    });
    $db->event(\Conpoz\Core\Lib\Db\DBQuery::TIMING_BEFORE, \Conpoz\Core\Lib\Db\DBQuery::ACTION_INSERT, function () use ($db) {
        $date = date('Y-m-d H:i:s');
        $db->data['created_at'] = $date;
        $db->data['updated_at'] = $date;
    });
    $db->event(\Conpoz\Core\Lib\Db\DBQuery::TIMING_BEFORE, \Conpoz\Core\Lib\Db\DBQuery::ACTION_UPDATE, function () use ($db) {
        $db->data['updated_at'] = date('Y-m-d H:i:s');
    });
    return $db;
};

$bag->req = function () {
    $req = new \Conpoz\Core\Lib\Util\Request();
    return $req;
};

$bag->sess = function () {
    /**
     *  cookie expireTime = 0,
     *  path = /,
     *  domain = *.conpoz.lo,
     *  access need https = false, 
     *  httpOnly = true (javascript can't access)
     * */
    session_set_cookie_params(0, '/', '.conpoz.lo', false, true);
    return new \Conpoz\Core\Lib\Util\Session();
};

$bag->mem = function () use ($config) {
    $mem = new \memcached();
    $mem->setOption(\Memcached::OPT_PREFIX_KEY, 'conpoz.lo.');
    $mem->addServer($config->mem['host'], $config->mem['port']);
    return $mem;
};

$bag->imgMng = function () {
    return new \Conpoz\Core\Lib\Util\ImageManager();
};

$bag->net = function () {
    return new \Conpoz\Core\Lib\Util\Network();
};

$bag->tool = function () {
    return new \Conpoz\Core\Lib\Util\Tool();
};

$bag->validator = function () {
    return new \Conpoz\Core\Lib\Util\Validator();
};

return $bag;
