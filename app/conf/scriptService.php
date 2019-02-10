<?php
$bag = new \Conpoz\Core\Lib\Util\Container();

$bag->config = $config;
$bag->dbquery = function () use ($config) {
    $db = new \Conpoz\Core\Lib\Db\DBQuery($config->db);
    $db->persistent = true;
    $db->emulatePrepare = true;
    $db->setSqlErrorHandler(function ($rh) {
        throw new \Conpoz\Core\Lib\Db\DBQuery\Exception(json_encode($rh->error()));
    });
    $db->event(DBQuery::TIMING_BEFORE, DBQuery::ACTION_INSERT, function () use ($db) {
        $db->data['created'] = date('Y-m-d H:i:s');
    });
    $db->event(DBQuery::TIMING_BEFORE, DBQuery::ACTION_UPDATE, function () use ($db) {
        $db->data['updated'] = date('Y-m-d H:i:s');
    });
    return $db;
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
