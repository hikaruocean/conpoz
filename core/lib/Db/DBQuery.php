<?php 

namespace Conpoz\Core\Lib\Db;
class DBQuery 
{
    const AUTO_RESOURCE_ID = -1;
    const MASTER_RESOURCE_ID = 0;
    const SLAVE_RESOURCE_ID = 1;
    static public $bindType = array(
        'boolean' => \PDO::PARAM_BOOL,
        'integer' => \PDO::PARAM_INT,
        'others' => \PDO::PARAM_STR,
        );
    public $db = array();
    public $sth = null;
    public $success = array(false, false);
    public $deadlockRetryTimes = 3;
    public $deadlockUsleepTime = 300000; //0.3s
    public $focusMaster = false;
    private $errorInfo = null;
    private $dsnSet = array();
    private $username = array();
    private $password = array();
    private $sqlErrorHandler = null;
    protected $table;
    protected $data;

    public function __construct($dsnSet) 
    {
        // set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
        //     if (strpos($errstr, 'PDO::__construct(): MySQL server has gone away') !== false || strpos($errstr, "Error while sending QUERY packet") !== false) {
        //         throw new \Conpoz\Core\Lib\Db\DBQuery\Exception("Mysql server has gone away", 2006);
        //     }
        //     return false;
        // }, E_WARNING);

        register_shutdown_function(function(\Conpoz\Core\Lib\Db\DBQuery $obj) {
            if($obj->success[SELF::MASTER_RESOURCE_ID] && $obj->db[SELF::MASTER_RESOURCE_ID]->inTransaction()) {
                if(!$obj->db[SELF::MASTER_RESOURCE_ID]->rollBack()) {
                    $obj->db = null;
                }
            }
        }, $this);
        
        foreach ($dsnSet as $key => $val) {
            switch ($key) {
                case 'master':
                    if (empty($val['adapter']) || empty($val['dbname']) || empty($val['host']) || empty($val['username']) || empty($val['password'])) {
                        throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql Db master miss params');
                    }
                    $val['charset'] = isset($val['charset']) && !empty($val['charset']) ? $val['charset'] : 'utf8';
                    $this->dsnSet[0] = $val['adapter'] . ':dbname=' .$val['dbname'] . ';host=' . $val['host'] . ';charset=' . $val['charset'];
                    $this->username[0] = $val['username'];
                    $this->password[0] = $val['password'];
                    break;
                case 'slave':
                    foreach ($val as $slaveKey => $slaveVal) {
                        if (empty($slaveVal['adapter']) || empty($slaveVal['dbname']) || empty($slaveVal['host']) || empty($slaveVal['username']) || empty($slaveVal['password'])) {
                            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql Db slave ' . $slaveKey . ' miss params');
                        }
                        $slaveIndex = $slaveKey + 1;
                        $slaveVal['charset'] = isset($slaveVal['charset']) && !empty($slaveVal['charset']) ? $slaveVal['charset'] : 'utf8';
                        $this->dsnSet[$slaveIndex] = $slaveVal['adapter'] . ':dbname=' .$slaveVal['dbname'] . ';host=' . $slaveVal['host'] . ';charset=' . $slaveVal['charset'];
                        $this->username[$slaveIndex] = $slaveVal['username'];
                        $this->password[$slaveIndex] = $slaveVal['password'];
                    }
                    break;
                default:
                    throw new \Conpoz\Core\Lib\Db\DBQuery\Exception("Mysql Db config format error");
            }
        }
    }

    public function connect ($dbEnv = SELF::MASTER_RESOURCE_ID)
    {
        try {
            switch ($dbEnv) {
                case SELF::MASTER_RESOURCE_ID:
                    $this->db[SELF::MASTER_RESOURCE_ID] = new \PDO($this->dsnSet[0], $this->username[0], $this->password[0], array(
                        \PDO::ATTR_PERSISTENT => true
                    ));
                    break;
                case SELF::SLAVE_RESOURCE_ID:
                    $slaveIndex = rand(1, count($this->dsnSet) - 1);
                    $this->db[SELF::SLAVE_RESOURCE_ID] = new \PDO($this->dsnSet[$slaveIndex], $this->username[$slaveIndex], $this->password[$slaveIndex], array(
                        \PDO::ATTR_PERSISTENT => true
                    ));
                    break;
            }
        } catch (\PDOException $e) {
            $this->errorInfo = $e->getMessage();
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception($this->errorInfo);
            return false;
        }
        $this->success[$dbEnv] = true;
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
        $this->focusMaster = true;
        if (!$this->success[SELF::MASTER_RESOURCE_ID]) {
            $this->connect(SELF::MASTER_RESOURCE_ID);
        }
        $this->db[SELF::MASTER_RESOURCE_ID]->beginTransaction();
        return $this;
    }

    protected function beforeCommit()
    {
        //do nothign
    }

    public function commit() 
    {
        if (!$this->success[SELF::MASTER_RESOURCE_ID]) {
            $this->connect(SELF::MASTER_RESOURCE_ID);
        }
        $this->beforeCommit();
        $return = $this->db[SELF::MASTER_RESOURCE_ID]->commit();
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
        if (!$this->success[SELF::MASTER_RESOURCE_ID]) {
            $this->connect(SELF::MASTER_RESOURCE_ID);
        }
        $this->beforeRollback();
        $return = $this->db[SELF::MASTER_RESOURCE_ID]->rollBack();
        $this->afterRollback($return);
        return $return;
    }

    protected function afterRollback($success)
    {
        //do nothign
    }

    public function execute($sql, array $params = array(), $dbEnv = SELF::AUTO_RESOURCE_ID) 
    {
        if (empty($sql)) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql sql statement is required');
        }
        
        $resourceIndex = null;
        switch ($dbEnv) {
            case SELF::MASTER_RESOURCE_ID:
                $resourceIndex = SELF::MASTER_RESOURCE_ID;
                $this->focusMaster = true;
                break;
            case SELF::SLAVE_RESOURCE_ID:
                $resourceIndex = SLAVE_RESOURCE_ID;
                break;
            default:
                if ($this->focusMaster) {
                    $resourceIndex = SELF::MASTER_RESOURCE_ID;
                } else {
                    if (stripos(trim($sql), 'SELECT') === 0) {
                        $resourceIndex = SELF::SLAVE_RESOURCE_ID;
                    } else {
                        $resourceIndex = SELF::MASTER_RESOURCE_ID;
                        $this->focusMaster = true;
                    }
                }
        }
        
        $this->sth = null;
        if (!$this->success[$resourceIndex]) {
            $this->connect($resourceIndex);
        } 
        $this->sth = $this->db[$resourceIndex]->prepare($sql);  
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
        
        /**
        * execute and handle deadlock, and redo for setting times
        **/
        $retry = 0;
        while (true) {
            try {
                $success = $this->sth->execute();
                /**
                * executed and no exception;
                **/
                break;
            } catch (\PDOException $e) {
                if ($retry <= $this->deadlockRetryTimes) {
                    throw $e;
                }
                /**
                * $e->errorInfo[0]==40001 (ISO/ANSI) Serialization failure, e.g. timeout or deadlock;
                * $e->errorInfo[1]==1213 (MySQL SQLSTATE) ER_LOCK_DEADLOCK
                */
                if ($e->errorInfo[0]==40001 && $exc->errorInfo[1]==1213) {
                    $retry ++;
                    usleep($this->deadlockUsleepTime);
                } else {
                    throw $e;
                }
            }
        }
        
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
        $rh = $this->execute($sql, $this->data, SELF::MASTER_RESOURCE_ID);
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
        $rh = $this->execute($sql, array_merge($paramsAry, $params), SELF::MASTER_RESOURCE_ID);
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
        $rh = $this->execute($sql, $params, SELF::MASTER_RESOURCE_ID);
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
