<?php 

namespace Conpoz\Lib\Db;
class DBQuery 
{
    public $db = null;
    public $sth = null;
    public $success = false;
    private $errorInfo = null;
    private $dsn = null;
    private $username = null;
    private $password = null;
    protected $table;
    protected $data;

    public function __construct($params) 
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
            if (strpos($errstr, 'PDO::__construct(): MySQL server has gone away') !== false || strpos($errstr, "Error while sending QUERY packet") !== false) {
                throw new \Exception("mysql server has gone away", 2006);
            }
            return false;
        }, E_WARNING);

        register_shutdown_function(function(\Conpoz\Lib\Db\DBQuery $obj) {
            if($obj->success && $obj->db->inTransaction()) {
                if(!$obj->db->rollBack()) {
                    $obj->db = null;
                }
            }
        }, $this);

        if (empty($params['adapter']) || empty($params['dbname']) || empty($params['host']) || empty($params['username']) || empty($params['password'])) {
            throw new \Exception("db connected miss params");
        }

        $params['charset'] = isset($params['charset']) && !empty($params['charset']) ? $params['charset'] : 'utf8';
        $this->dsn = $params['adapter'] . ':dbname=' .$params['dbname'] . ';host=' . $params['host'] . ';charset=' . $params['charset'];
        $this->username = $params['username'];
        $this->password = $params['password'];
        $this->connect();
    }

    public function connect ()
    {
        try {
            $this->db = new \PDO($this->dsn, $this->username, $this->password, array(
                \PDO::ATTR_PERSISTENT => true
            ));
        }
        catch (\PDOException $e) {
            $this->errorInfo = $e->getMessage();
            return false;
        }
        $this->success = true;
        return true;
    }

    public function success() 
    {
        return $this->success;
    }

    public function error() 
    {
        return $this->errorInfo;
    }

    public function begin() 
    {
        if (!$this->success) {
            return false;
        }
        $this->db->beginTransaction();
        return $this;
    }

    public function commit() 
    {
        if (!$this->success) {
            return false;
        }
        return $this->db->commit();
    }

    public function rollback() 
    {
        if (!$this->success) {
            return false;
        }
        return $this->db->rollBack();
    }

    public function execute($sql, array $params = array()) 
    {
        $this->sth = null;
        if (!$this->success || empty($sql)) 
        {
            return false;
        }
        $this->sth = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            if (is_int($k)) {
                $this->sth->bindValue($k + 1, $v);
            } else {
                $this->sth->bindValue(':' . $k, $v);
            }
        }
        $success = $this->sth->execute();
        return new \Conpoz\Lib\Db\DBQuery\ResultHandler(array(
            'success' => $success,
            'sth' => $this->sth,
            'error' => $this->sth->errorInfo(),
            'rowCount' => $this->sth->rowCount()
        ));
    }

    protected function beforeInsert() 
    {
        //do nothing;
    }

    public function insert($table, array $data = array()) 
    {
        if (!$this->success || empty($table) || empty($data)) {
            return false;
        }
        $this->table = $table;
        $this->data = $data;
        $this->beforeInsert();
        $columnsStr = implode(',', array_keys($this->data));
        $columnsStr = '(' . $columnsStr . ')';
        $valuesBindStr = '(:' . implode(',:', array_keys($this->data)) . ')';
        $sql = 'INSERT INTO ' . $table .' '. $columnsStr . ' VALUES ' . $valuesBindStr;
        $rh = $this->execute($sql, $this->data);
        $rh->lastInsertId = $this->db->lastInsertId();
        $this->afterInsert();
        return $rh;
    }

    protected function afterInsert ()
    {
        //do nothing;
    }

    protected function beforeUpdate() 
    {
        //do nothing;
    }

    public function update($table, array $data, $conditions = '1', array $params = array()) 
    {
        if (!$this->success || empty($data) || empty($table)) {
            return false;
        }
        if (is_null($conditions)) {
            $conditions = '1';
        }
        $this->table = $table;
        $this->data = $data;
        $this->beforeUpdate();
        $updateStr = '';
        $paramsAry = array();
        foreach ($this->data as $columnName => $columnValue) {
            $updateStr .= ' ' . $columnName . ' =  :d_' . $columnName . ',';
            $paramsAry['d_' . $columnName] = $columnValue;
        }
        $updateStr = trim($updateStr, ',');
        $sql = 'UPDATE ' . $table . ' SET ' . $updateStr . ' WHERE ' . $conditions;
        $rh = $this->execute($sql, array_merge($paramsAry, $params));
        $this->afterUpdate();
        return $rh;
    }

    protected function afterUpdate() 
    {
        //do nothing;
    }

    protected function beforeDelete ()
    {
        //do nothing;
    }

    public function delete($table, $conditions = '1', array $params = array()) 
    {
        if (!$this->success || empty($table)) {
            return false;
        }
        if (is_null($conditions)) {
            $conditions = '1';
        }
        $this->table = $table;
        $this->beforeDelete();
        $sql = 'DELETE FROM ' . $table . ' WHERE ' . $conditions;
        $rh = $this->execute($sql, $params);
        $this->afterDelete();
        return $rh;
    }

    protected function afterDelete ()
    {
        //do nothing;
    }

    public function getData ()
    {
        return $this->data;
    }
}