<?php
namespace Conpoz\Core\Lib\Util;

class Note
{
    public $instance = array();

    public function __set ($instanceName, $instance)
    {
        $this->instance[$instanceName] = $instance;
    }

    public function __get ($instanceName)
    {
        if (!isset($this->instance[$instanceName])) {
            throw new \Exception('note [' . $instanceName . '] not found');
        }
        return $this->instance[$instanceName];
    }

    public function __isset($instanceName)
    {
        return isset($this->instance[$instanceName]);
    }

    public function __unset($instanceName)
    {
        unset($this->instance[$instanceName]);
    }
}
