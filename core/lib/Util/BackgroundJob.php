<?php 
namespace Conpoz\Core\Lib\Util;

class BackgroundJob
{
    public $dbquery = null;
    public function __construct ($dbquery)
    {
        $this->dbquery = $dbquery;
    }
    
    public function dispatch ($name, $params = '')
    {
        $rh = $this->dbquery->insert('job_queue', array('name' => $name, 'params' => $params));
        return $rh->success();
    }
    
    public function setDBQuery ($dbquery)
    {
        $this->dbquery = $dbquery;
        return $this;
    }
}