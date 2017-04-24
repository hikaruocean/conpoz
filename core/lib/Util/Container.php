<?php
namespace Conpoz\Core\Lib\Util;

class Container
{
    public static $service = array();

    public static function getService($serviceName)
    {
        if (!isset(self::$service[$serviceName])) {
            return null;
        }
        if (is_callable(self::$service[$serviceName])) {
            self::$service[$serviceName] = self::$service[$serviceName]->__invoke();
        }
        return self::$service[$serviceName];
    }

    public function __set ($serviceName, $instance)
    {
        self::$service[$serviceName] = $instance;
    }

    public function __get ($serviceName)
    {
        if (!isset(self::$service[$serviceName])) {
            return null;
        }
        if (is_callable(self::$service[$serviceName])) {
            self::$service[$serviceName] = self::$service[$serviceName]->__invoke();
        }
        return self::$service[$serviceName];
    }
}
