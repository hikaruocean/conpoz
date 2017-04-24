<?php
namespace Conpoz\App\Controller;

abstract class BaseController
{
    public function init($bag)
    {
        /**
         * ACL
         */
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
}
