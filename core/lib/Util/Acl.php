<?php 
namespace Conpoz\Core\Lib\Util;

class ACL
{
    public $roles = array();
    public function allow($role, $controllerName, $actionName)
    {
        $this->roles[$role][$controllerName][$actionName] = true;
        return true;
    }

    public function isAllow($role, $controllerName, $actionName)
    {
        if (isset($this->roles[$role][$controllerName]['*']) && $this->roles[$role][$controllerName]['*'] === true) {
            return true;
        }
        if (isset($this->roles[$role][$controllerName][$actionName]) &&$this->roles[$role][$controllerName][$actionName] === true) {
            return true;
        }
        return false;
    }
}