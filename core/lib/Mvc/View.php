<?php 

namespace Conpoz\Lib\Mvc;

class View
{
    public $viewRoot;
    public $viewPath;
    public $data = array();
    public function __construct()
    {
        $this->viewRoot = APP_PATH . '/view/';
    }

    public function render($__conpozViewPath = null)
    {
        $__conpozViewPath = $this->viewRoot . $__conpozViewPath . '.php';
        if (is_null($__conpozViewPath) || empty($__conpozViewPath) || !is_file($__conpozViewPath)) {
            return false;
        }
        foreach($this->data as $__k => &$__v) {
            ${$__k} = &$__v;
        }
        $this->viewPath = &$__conpozViewPath;
        unset($__varsAry, $__k, $__v, $__conpozViewPath);
        require($this->viewPath);
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
}