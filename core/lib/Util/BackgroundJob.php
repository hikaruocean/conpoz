<?php
namespace Conpoz\Core\Lib\Util;

class BackgroundJob
{
    public $dbquery = null;
    public $queueName = 'job_queue';
    public function __construct ($dbquery)
    {
        $this->dbquery = $dbquery;
    }

    public function dispatch ($name, $params = '', $queueName = null)
    {
        if (is_null($queueName)) {
            $queueName = $this->queueName;
        }
        $rh = $this->dbquery->insert($queueName, array('name' => $name, 'params' => $params));
        return $rh->success();
    }

    public function setDBQuery ($dbquery)
    {
        $this->dbquery = $dbquery;
        return $this;
    }
}
