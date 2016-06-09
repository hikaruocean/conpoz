<?php 

namespace Conpoz\Lib\Mvc;

class View extends \stdClass
{
    const VIEW_ROOT = APP_PATH . '/view/';
    public function __construct()
    {

    }

    public function render($path = null)
    {
        if (!$path) {
            return false;
        }
        require(SELF::VIEW_ROOT . $path . '.php');
    }
}