<?php
namespace Conpoz\Core\Lib\Util;

class AclCsvParser
{
    public $dbquery = null;
    public $csvFolder = CONPOZ_PATH . '/AclCsv';
    public function __construct (\Conpoz\Core\Lib\Db\DBQuery $dbquery, String $csvFolder = null)
    {
        $this->dbquery = $dbquery;
        if (!is_null($csvFolder) && is_dir(realpath($csvFolder))) {
            $this->csvFolder = realpath($csvFolder);
        }
    }

    public function start ()
    {
        $this->dbquery->begin();
        try {
            $this->dbquery->delete("acl_roles", "1");
            $this->dbquery->delete("acl_controllers", "1");
            $this->dbquery->delete("acl_actions", "1");
            $this->dbquery->delete("acl_grants", "1");
            $dh = opendir($this->csvFolder);
            while(($filename = readdir($dh)) !== false){
                if (filetype($this->csvFolder . '/' . $filename) != 'file' || preg_match('/^.+\.csv$/', $filename) !== 1) {
                    continue;
                }
                $this->parse($filename);
            }
            $this->dbquery->commit();
            echo 'Success!!' . PHP_EOL;
        } catch (\Exception $e) {
            $this->dbquery->rollback();
            throw $e;
        }
        $this->dbquery->execute("OPTIMIZE TABLE acl_roles, acl_controllers, acl_actions, acl_grants");
    }

    public function parse(String $filename): void
    {
        $fh = fopen($this->csvFolder .'/' . $filename, 'r');
        $row = fgetcsv($fh);
        /*
        check the sheet type
        */
        switch($row[0]) {
            case 'ROLES':
                $this->parseRoles($fh);
                break;
            case 'BINDING':
                $this->parseBinding($fh);
                break;
            default:
                throw new \Exception('Sheet Type Error');
        }
    }

    private function parseBinding($fh)
    {
        $rowNo = 1;
        $controllerId = null;
        while (($rowAry = fgetcsv($fh)) !== false) {
            $rowNo++;
            if ($rowNo === 3) {
                $rh = $this->dbquery->insert('acl_controllers', array('name' => $rowAry[0], 'descript' => $rowAry[1]));
                $controllerId = $rh->lastInsertId();
                continue;
            }
            if ($rowNo >= 5 && $rowAry[0] !== '') {
                $rowAryLen = count($rowAry);
                $name = $rowAry[0];
                $descript = $rowAry[1];
                $rh = $this->dbquery->insert('acl_actions', array('controller_id' => $controllerId, 'name' => $name, 'descript' => $descript));
                $actionId = $rh->lastInsertId();
                for ($i = 2; $i < $rowAryLen; $i ++) {
                    $roleId = $rowAry[$i];
                    if ($roleId == 'NULL') {
                        continue;
                    }
                    $this->dbquery->insert('acl_grants', array('role_id' => $roleId, 'controller_id' => $controllerId, 'action_id' => $actionId));
                }
            }
        }
    }

    private function parseRoles($fh)
    {
        $rowNo = 1;
        while (($rowAry = fgetcsv($fh)) !== false) {
            $rowNo++;
            if ($rowNo < 4) {
                continue;
            }
            $this->dbquery->insert("acl_roles", array('role_id' => $rowAry[0], 'name' => $rowAry[1], 'admin' => $rowAry[2]));
        }
    }
}
