<?php 
namespace Conpoz\Lib\Db\DBQuery;

class ResultHandler
{
    public $success = false;
    public $sth = null;
    public $error = null;
    public $lastInsertId = null;
    public $rowCount = null;
    public function __construct(array $params = array()) 
    {
        if (isset($params['success']) && $params['success']) {
            $this->success = true;
        }
        if (isset($params['sth']) && $params['sth']) {
            $this->sth = $params['sth'];
        }
        if(isset($params['error']) && $params['error']) {
            $this->error = $params['error'];
        }
        if(isset($params['rowCount']) && $params['rowCount']) {
            $this->rowCount = $params['rowCount'];
        }
    }

    public function fetch() 
    {
        if (is_null($this->sth)) {
            return false;
        }
        return $this->sth->fetch(\PDO::FETCH_OBJ);
    }

    public function success() 
    {
        return $this->success;
    }

    public function error() 
    {
        return $this->error;
    }

    public function lastInsertId() 
    {
        return $this->lastInsertId;
    }

    public function rowCount() 
    {
        return $this->rowCount;
    }
}