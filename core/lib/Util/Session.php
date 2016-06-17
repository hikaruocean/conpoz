<?php 
namespace Conpoz\Lib\Util;

class Session
{
    public function __construct() 
    {
        $this->start();
    }

    public function start()
    {
        session_start();   
    }

    public function truncate () 
    {
        session_destroy();
        $_SESSION = array();
    }

    public function  forceSet(array $data = array()) 
    {
        $_SESSION = $data;
    }

    public function dump()
    {
        return $_SESSION;
    }

    public function __get ($name) 
    {
        if (!isset($_SESSION[$name])) {
            return null;
        }
        return $_SESSION[$name];
    }

    public function __set ($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function __unset ($name)
    {
        if (!isset($_SESSION[$name])) {
            return false;
        }
        unset($_SESSION[$name]);
        return true;
    }
}