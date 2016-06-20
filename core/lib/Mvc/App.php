<?php 

namespace Conpoz\Lib\Mvc;

class App extends \stdClass
{
    public $config;
    public $controllerName;
    public $actionName;
    public function __construct($bag)
    {
        $this->bag = $bag;
    }

    public function run(&$config = null)
    {
        $configRouteDefault = array(
            'defaultController' => 'Index',
            'defaultAction' => 'index',
            '404Controller' => 'Error',
            '404Action' => 'http404',
            );

        /**
         * get controller, get action
         * Gen controllerObject
         */
        $config['route'] = array_merge($configRouteDefault, $config['route']);
        $this->config = &$config;
        $uri = isset($_GET['_url']) ? $_GET['_url'] : '';
        $uriAry = explode('/', $uri);
        array_shift($uriAry);
        $controller = isset($uriAry[0]) && !empty($uriAry[0]) ? $uriAry[0] : $this->config['route']['defaultController'];
        $action = isset($uriAry[1]) && !empty($uriAry[1]) ? $uriAry[1] : $this->config['route']['defaultAction'];
        $this->dispatch($controller, $action);
    }

    public function dispatch ($controller, $action)
    {
        $controller = ucfirst($controller);
        if (!class_exists('Conpoz\\App\\Controller\\' . $controller)) {
            $controller = $this->config['route']['404Controller'];
            $action = $this->config['route']['404Action'];
            $controllerClass = '\\Conpoz\\App\\Controller\\' . $controller;
            $controllerObject = new $controllerClass();
        } else {
            $controllerClass = '\\Conpoz\\App\\Controller\\' . $controller;
            $controllerObject = new $controllerClass();
            if (!method_exists($controllerObject, $action . 'Action')) {
                $controller = $this->config['route']['404Controller'];
                $action = $this->config['route']['404Action'];
                $controllerClass = '\\Conpoz\\App\\Controller\\' . $controller;
                $controllerObject = new $controllerClass();
            }
        }

        /**
         * Gen MVC structure
         */
        
        $this->controllerName = $controller;
        $this->actionName = $action;
        $this->controller = $controllerObject;
        $controllerObject->app = $this;
        $controllerObject->bag = $this->bag;
        $controllerObject->model = new \Conpoz\Lib\Mvc\Model($this->bag);
        $controllerObject->view = new \Conpoz\Lib\Mvc\View();
        if (method_exists($controllerObject, 'init')) {
            if ($controllerObject->init() === false) {
                return false;
            }
        }
        $controllerObject->{$action . 'Action'}();
    }
}