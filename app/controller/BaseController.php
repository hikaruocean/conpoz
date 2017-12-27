<?php
namespace Conpoz\App\Controller;

abstract class BaseController
{
    public function init ($bag)
    {
        /**
        * ACL
        */
        // if (false === $this->acl()) {
        //     return false;
        // }
        /**
        * MIDDLEWARE
        */
        if (false === $this->middleware()) {
            return false;
        }
        return true;
    }
    
    public function acl ()
    {
        $bag = $this->bag;
        if (!$bag->sess->user_id || !$bag->sess->user_role) {
            $roles = ['guest'];
        } else {
            $roles = ['admin'];
        }

        $aclCache = $bag->mem->get('ACL');
        if (!$aclCache) {
            $acl = require(APP_PATH . '/conf/acl.php');
            $bag->mem->set('ACL', serialize($acl));
        } else {
            require_once(CORE_PATH . '/lib/Util/Acl.php');
            $acl = unserialize($aclCache);
        }

        /**
         *  Check if the Role have access to the controller (resource)
         */
        foreach ($roles as $role) {
            $allow = $acl->isAllow($role, $this->app->controllerName, $this->app->actionName);
            if ($allow === true) { //有一個 role 能使用 currentController/Action 就允許進入
                break;
            }
        }

        if ($allow !== true) { //處理未授權
            $this->app->dispatch('Index', 'index');
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
            $middlewareFullName::run($this);
        }
        
        return true;
    }
}
