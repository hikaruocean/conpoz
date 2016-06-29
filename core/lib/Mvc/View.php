<?php 

namespace Conpoz\Core\Lib\Mvc;

class View
{
    public $viewRoot;
    public $view = array();
    public function __construct()
    {
        $this->viewRoot = APP_PATH . '/view';
    }

    public function addView($viewPath) 
    {
        array_push($this->view, $viewPath);
    }

    public function getView()
    {
        $viewPath = array_shift($this->view);
        $viewPath =  $this->viewRoot . $viewPath . '.php';
        if (!($realViewPath = realpath($viewPath))) {
            throw new \Exception('Not Found View ' . $viewPath);
            return false;
        }
        return $realViewPath;
    }
}