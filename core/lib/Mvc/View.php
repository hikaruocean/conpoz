<?php 

namespace Conpoz\Lib\Mvc;

class View extends \stdClass
{
    public $__conpozViewRoot;
    public function __construct()
    {
        $this->__conpozViewRoot = APP_PATH . '/view/';
    }

    public function render($__conpozViewPath = null)
    {
        $__conpozViewPath = $this->__conpozViewRoot . $__conpozViewPath . '.php';
        unset($this->__conpozViewRoot);
        if (is_null($__conpozViewPath) || empty($__conpozViewPath) || !is_file($__conpozViewPath)) {
            return false;
        }
        $__varsAry = get_object_vars($this);
        foreach($__varsAry as $__k => &$__v) {
            ${$__k} = &$__v;
        }
        $this->viewPath = &$__conpozViewPath;
        unset($__varsAry, $__k, $__v, $__conpozViewPath);
        require($this->viewPath);
    }
}