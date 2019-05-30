<?php
namespace Conpoz\Core\Util\Lib;

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
}
