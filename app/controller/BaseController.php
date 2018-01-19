<?php
namespace Conpoz\App\Controller;

abstract class BaseController
{
    public function init ($bag)
    {
        /**
        * MIDDLEWARE
        */
        if (false === $this->middleware()) {
            return false;
        }
        return true;
    }
    
    public function middleware ()
    {
        $bag = $this->bag;
        $preparedMiddlewareAry = array();
        if (isset($bag->config->middlewareBind['*'])) {
            $middlewareArray = (array) $bag->config->middlewareBind['*'];
            foreach ($middlewareArray as $middlewareName) {
                $middlewareFullName = '\\Conpoz\App\Middleware\\' . $middlewareName;
                if (!in_array($middlewareFullName, $preparedMiddlewareAry)) {
                    $preparedMiddlewareAry[] = $middlewareFullName;
                }
            }
        }
        
        if (isset($bag->config->middlewareBind[$this->app->controllerName]['*'])) {
            $middlewareArray = (array) $bag->config->middlewareBind[$this->app->controllerName]['*'];
            foreach ($middlewareArray as $middlewareName) {
                $middlewareFullName = '\\Conpoz\App\Middleware\\' . $middlewareName;
                $key = array_search($middlewareFullName, $preparedMiddlewareAry);
                if (false === $key) {
                    $preparedMiddlewareAry[] = $middlewareFullName;
                } else {
                    unset($preparedMiddlewareAry[$key]);
                    $preparedMiddlewareAry[] = $middlewareFullName;
                }
            }
        }
        
        if (isset($bag->config->middlewareBind[$this->app->controllerName][$this->app->actionName])) {
            $middlewareArray = (array) $bag->config->middlewareBind[$this->app->controllerName][$this->app->actionName];
            foreach ($middlewareArray as $middlewareName) {
                $middlewareFullName = '\\Conpoz\App\Middleware\\' . $middlewareName;
                $key = array_search($middlewareFullName, $preparedMiddlewareAry);
                if (false === $key) {
                    $preparedMiddlewareAry[] = $middlewareFullName;
                } else {
                    unset($preparedMiddlewareAry[$key]);
                    $preparedMiddlewareAry[] = $middlewareFullName;
                }
            }
        }
        
        foreach ($preparedMiddlewareAry as $middlewareFullName) {
            if(!$middlewareFullName::run($this)) {
                return false;
            }
        }
        
        return true;
    }
}
