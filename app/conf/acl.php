<?php 
$closure = function ($db, $config) {
    $acl = new \Conpoz\Core\Lib\Util\Acl();
    foreach ($config->ACL['publicResources'] as $resourceAry) {
        $acl->allow($config->ACL['publicRole'], $resourceAry[0], $resourceAry[1]);
    }

    $adminsId = array();
    /**
     * register role
     */
    $rh = $db->execute('SELECT r.role_id,r.admin FROM acl_roles r');
    while ($role = $rh->fetch()) {
        /**
         * find admin
         */
        if ($role->admin === 'Y') {
            $adminsId[] = $role->role_id;
        }
        /**
         * grant all role to public resource
         */
        foreach ($config->ACL['publicResources'] as $resourceAry) {
            $acl->allow($role->role_id, $resourceAry[0], $resourceAry[1]);
        }
    }

    /**
     * register controller/action
     */
    $rh = $db->execute('SELECT c.name cn, a.name ans  FROM acl_controllers c, acl_actions a WHERE c.controller_id = a.controller_id');
    while ($obj = $rh->fetch()) {
        $ansAry = explode(',', $obj->ans);
        foreach ($ansAry as $an) {
            /**
             * grant admin to all resourece
             */
            foreach ($adminsId as $adminId) {
                $acl->allow($adminId, $obj->cn, $an);
            }
        }
    }
    
    /**
     * grant resource to role
     */
    $rh = $db->execute("SELECT r.role_id,c.name as cn,a.name as ans FROM acl_roles  AS r, acl_grants  AS g, acl_controllers AS c, acl_actions AS a WHERE r.admin != 'Y' AND r.role_id = g.role_id AND g.controller_id = c.controller_id AND g.action_id = a.action_id");
    while ($obj = $rh->fetch()) {
        $ansAry = explode(',', $obj->ans);
        foreach($ansAry as $an) {
            $acl->allow($obj->role_id, $obj->cn, $an);
        }
    }

    return $acl;
};
return $closure($controller->bag->dbquery, $controller->bag->config);