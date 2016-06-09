<?php 
namespace Conpoz\Lib\Util;

class Container
{
    public $service = array();
    public function __set ($serviceName, $instance)
    {
        $this->service[$serviceName] = $instance;
    }

    public function __get ($serviceName)
    {
        if (!isset($this->service[$serviceName])) {
            return null;
        }
        if (is_callable($this->service[$serviceName])) {
            $this->service[$serviceName] = $this->service[$serviceName]();
        }
        return $this->service[$serviceName];
    }
}