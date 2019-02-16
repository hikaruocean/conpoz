<?php

namespace Conpoz\Core\Lib\Mvc;

class FastRouteApp
{
    public $config;
    public $controllerName;
    public $actionName;
    public function __construct($bag)
    {
        $this->bag = $bag;
    }

    public function run($config = null)
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
        $config->route = array_merge($configRouteDefault, $config->route);
        $this->config = $config;
        $uri = rtrim(isset($_GET['_url']) ? $_GET['_url'] : '', '/');

        $dispatcher = require(APP_PATH . '/conf/route.php');
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = rawurldecode($uri);
        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                list($controller, $action) = explode('::', $handler);
                $this->dispatch($controller, $action, $vars);
                break;
        }
    }

    public function dispatch ($controller, $action, $vars)
    {
        if (!class_exists('Conpoz\\App\\Controller\\' . $controller)) {
            $controller = $this->config->route['404Controller'];
            $action = $this->config->route['404Action'];
            $controllerClass = '\\Conpoz\\App\\Controller\\' . $controller;
            $controllerObject = new $controllerClass();
        } else {
            $controllerClass = '\\Conpoz\\App\\Controller\\' . $controller;
            $controllerObject = new $controllerClass();
            if (!is_callable(array($controllerObject, $action . 'Action'))) {
                $controller = $this->config->route['404Controller'];
                $action = $this->config->route['404Action'];
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
        $this->model = new \Conpoz\Core\Lib\Mvc\Model($this);
        $controllerObject->app = $this;
        $controllerObject->bag = $this->bag;
        $controllerObject->model = $this->model;
        $controllerObject->view = new \Conpoz\Core\Lib\Mvc\View();
        if (method_exists($controllerObject, 'init')) {
            if ($controllerObject->init($this->bag) === false) {
                return false;
            }
        }
        $controllerObject->urlVars = $vars;
        $controllerObject->{$action . 'Action'}($this->bag);
    }
}
