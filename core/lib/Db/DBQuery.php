<?php 

namespace Conpoz\Core\Lib\Db;
class DBQuery 
{
    static public $bindType = array(
        'boolean' => \PDO::PARAM_BOOL,
        'integer' => \PDO::PARAM_INT,
        'others' => \PDO::PARAM_STR,
        );
    public $db = null;
    public $sth = null;
    public $success = false;
    private $errorInfo = null;
    private $dsn = null;
    private $username = null;
    private $password = null;
    private $sqlErrorHandler = null;
    protected $table;
    protected $data;

    public function __construct($params) 
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
            if (strpos($errstr, 'PDO::__construct(): MySQL server has gone away') !== false || strpos($errstr, "Error while sending QUERY packet") !== false) {
                throw new \Conpoz\Core\Lib\Db\DBQuery\Exception("Mysql server has gone away", 2006);
            }
            return false;
        }, E_WARNING);

        register_shutdown_function(function(\Conpoz\Core\Lib\Db\DBQuery $obj) {
            if($obj->success && $obj->db->inTransaction()) {
                if(!$obj->db->rollBack()) {
                    $obj->db = null;
                }
            }
        }, $this);

        if (empty($params['adapter']) || empty($params['dbname']) || empty($params['host']) || empty($params['username']) || empty($params['password'])) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception("Mysql Db connected miss params");
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
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception($this->errorInfo);
            return false;
        }
        $this->success = true;
        return true;
    }

    public function setSqlErrorHandler ($function)
    {
        $this->sqlErrorHandler = $function;
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
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql db is not connected');
        }
        $this->db->beginTransaction();
        return $this;
    }

    protected function beforeCommit()
    {
        //do nothign
    }

    public function commit() 
    {
        if (!$this->success) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql db is not connected');
        }
        $this->beforeCommit();
        $return = $this->db->commit();
        $this->afterCommit($return);
        return $return;
    }

    protected function afterCommit($success)
    {
        //do nothign
    }

    protected function beforeRollback()
    {
        //do nothign
    }

    public function rollback() 
    {
        if (!$this->success) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql db is not connected');
        }
        $this->beforeRollback();
        $return = $this->db->rollBack();
        $this->afterRollback($return);
        return $return;
    }

    protected function afterRollback($success)
    {
        //do nothign
    }

    public function execute($sql, array $params = array()) 
    {
        $this->sth = null;
        if (!$this->success) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql db is not connected');
        } 
        if (empty($sql)) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql sql statement is required');
        }
        $this->sth = $this->db->prepare($sql);  
        foreach ($params as $k => $v) {
            $bindType = SELF::$bindType['others'];
            if (isset(SELF::$bindType[gettype($v)])) {
                $bindType = SELF::$bindType[gettype($v)];
            }
            if (is_int($k)) {
                $this->sth->bindValue($k + 1, $v, $bindType);
            } else {
                $this->sth->bindValue(':' . $k, $v, $bindType);
            }
        }
        $success = $this->sth->execute();
        $rh = new \Conpoz\Core\Lib\Db\DBQuery\ResultHandler(array(
            'success' => $success,
            'sth' => $this->sth,
            'error' => $this->sth->errorInfo(),
            'rowCount' => $this->sth->rowCount()
        ));
        if (!$success && !is_null($this->sqlErrorHandler)) {
            $this->sqlErrorHandler->__invoke($rh);
        }
        return $rh;
    }

    protected function beforeInsert() 
    {
        //do nothing;
    }

    public function insert($table, array $data = array()) 
    {
        if (!$this->success) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql db is not connected');
        } 
        if (empty($data)) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql insert data is required');
        } 
        if (empty($table)) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql db table is required');
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
        $this->afterInsert($rh);
        return $rh;
    }

    protected function afterInsert ($rh)
    {
        //do nothing;
    }

    protected function beforeUpdate() 
    {
        //do nothing;
    }

    public function update($table, array $data, $conditions = null, array $params = array()) 
    {
        if (!$this->success) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql db is not connected');
        } 
        if (empty($data)) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql update data is required');
        } 
        if (empty($table)) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql db table is required');
        }
        if (is_null($conditions)) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql sql statement conditions is required');
        }
        $this->table = $table;
        if (is_array($table)) {
            $table = implode(', ', $table);
        }
        $this->data = $data;
        $this->beforeUpdate();
        $updateStr = '';
        $paramsAry = array();
        foreach ($this->data as $columnName => $columnValue) {
            $bindColumnName = str_replace('.', '_', $columnName);
            $updateStr .= ' ' . $columnName . ' =  :d_' . $bindColumnName . ',';
            $paramsAry['d_' . $bindColumnName] = $columnValue;
        }
        $updateStr = trim($updateStr, ',');
        $sql = 'UPDATE ' . $table . ' SET ' . $updateStr . ' WHERE ' . $conditions;
        $rh = $this->execute($sql, array_merge($paramsAry, $params));
        $this->afterUpdate($rh);
        return $rh;
    }

    protected function afterUpdate($rh) 
    {
        //do nothing;
    }

    protected function beforeDelete ()
    {
        //do nothing;
    }

    public function delete($table, $conditions = null, array $params = array()) 
    {
        if (!$this->success) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql db is not connected');
        }
        if (empty($table)) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql db table is required');
        }
        if (is_null($conditions)) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('SQL statement conditions is required');
        }
        $this->table = $table;
        if (is_array($table)) {
            $table = implode(', ', $table);
        }
        $this->beforeDelete();
        $sql = 'DELETE ' . $table . ' FROM ' . $table . ' WHERE ' . $conditions;
        $rh = $this->execute($sql, $params);
        $this->afterDelete($rh);
        return $rh;
    }

    protected function afterDelete ($rh)
    {
        //do nothing;
    }

    public function getData ()
    {
        return $this->data;
    }
}