<?php

namespace Conpoz\Core\Lib\Db;
class DBQuery
{
    const AUTO_RESOURCE_ID = -1;
    const MASTER_RESOURCE_ID = 0;
    const SLAVE_RESOURCE_ID = 1;
    const TIMING_BEFORE = 0;
    const TIMING_AFTER = 1;
    const ACTION_BEGIN = 0;
    const ACTION_COMMIT = 1;
    const ACTION_ROLLBACK = 2;
    const ACTION_EXECUTE = 3;
    const ACTION_INSERT = 4;
    const ACTION_UPDATE = 5;
    const ACTION_DELETE = 6;
    public static $bindType = array(
        'boolean' => \PDO::PARAM_BOOL,
        'integer' => \PDO::PARAM_INT,
        'others' => \PDO::PARAM_STR,
        );
    public $db = array();
    public $sth = null;
    public $success = array(false, false);
    public $deadlockRetryTimes = 3;
    public $deadlockUsleepTime = 300000; //0.3s

    /**
    * set following attr before DBQUERY::connect()
    **/
    public $masterDisableLoadbalance = true;
    public $persistent = true;
    public $emulatePrepare = true;

    private $singleResoure = true;
    private $focusMaster = false;
    private $errorInfo = null;
    private $dsnSet = array();
    private $username = array();
    private $password = array();
    private $sqlErrorHandler = null;
    protected $event = array(self::TIMING_BEFORE => array(), self::TIMING_AFTER => array());
    public $table;
    public $data;

    public function __construct($dsnSet)
    {
        register_shutdown_function(function(\Conpoz\Core\Lib\Db\DBQuery $obj) {
            if($obj->success[self::MASTER_RESOURCE_ID] && $obj->db[self::MASTER_RESOURCE_ID]->inTransaction()) {
                if(!$obj->db[self::MASTER_RESOURCE_ID]->rollBack()) {
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
                    $val['port'] = isset($val['port']) && !empty($val['port']) ? $val['port'] : '3306';
                    $val['charset'] = isset($val['charset']) && !empty($val['charset']) ? $val['charset'] : 'utf8';
                    $this->dsnSet[0] = $val['adapter'] . ':dbname=' .$val['dbname'] . ';host=' . $val['host'] . ';port=' . $val['port'] . ';charset=' . $val['charset'];
                    $this->username[0] = $val['username'];
                    $this->password[0] = $val['password'];
                    break;
                case 'slave':
                    foreach ($val as $slaveKey => $slaveVal) {
                        if (empty($slaveVal['adapter']) || empty($slaveVal['dbname']) || empty($slaveVal['host']) || empty($slaveVal['username']) || empty($slaveVal['password'])) {
                            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql Db slave ' . $slaveKey . ' miss params');
                        }
                        $slaveIndex = $slaveKey + 1;
                        $slaveVal['port'] = isset($slaveVal['port']) && !empty($slaveVal['port']) ? $slaveVal['port'] : '3306';
                        $slaveVal['charset'] = isset($slaveVal['charset']) && !empty($slaveVal['charset']) ? $slaveVal['charset'] : 'utf8';
                        $this->dsnSet[$slaveIndex] = $slaveVal['adapter'] . ':dbname=' .$slaveVal['dbname'] . ';host=' . $slaveVal['host'] . ';port=' . $slaveVal['port'] . ';charset=' . $slaveVal['charset'];
                        $this->username[$slaveIndex] = $slaveVal['username'];
                        $this->password[$slaveIndex] = $slaveVal['password'];
                    }
                    $this->singleResoure = false;
                    break;
                default:
                    throw new \Conpoz\Core\Lib\Db\DBQuery\Exception("Mysql Db config format error");
            }
        }
    }

    public function connect ($dbEnv = self::MASTER_RESOURCE_ID)
    {
        $retry = 0;
        $reconnectLimitTimes = 1;
        while (true) {
            try {
                switch ($dbEnv) {
                    case self::MASTER_RESOURCE_ID:
                        $this->db[self::MASTER_RESOURCE_ID] = new \PDO($this->dsnSet[0], $this->username[0], $this->password[0], array(
                            \PDO::ATTR_PERSISTENT => $this->persistent
                        ));
                        $this->db[self::MASTER_RESOURCE_ID]->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $this->emulatePrepare);
                        break;
                    case self::SLAVE_RESOURCE_ID:
                        if ($this->masterDisableLoadbalance) {
                            $slaveIndex = mt_rand(1, count($this->dsnSet) - 1);
                        } else {
                            $slaveIndex = mt_rand(0, count($this->dsnSet) - 1);
                        }
                        $this->db[self::SLAVE_RESOURCE_ID] = new \PDO($this->dsnSet[$slaveIndex], $this->username[$slaveIndex], $this->password[$slaveIndex], array(
                            \PDO::ATTR_PERSISTENT => $this->persistent
                        ));
                        $this->db[self::SLAVE_RESOURCE_ID]->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $this->emulatePrepare);
                        break;
                }
                break;
            } catch (\PDOException $e) {
                $this->errorInfo = $e->getMessage();
                throw new \Conpoz\Core\Lib\Db\DBQuery\Exception($this->errorInfo);
                return false;
            } catch (\Conpoz\Core\Lib\Db\DBQuery\Exception $e) {
                if ($e->getCode() != 2006) {
                    break;
                }
                if ($retry < $reconnectLimitTimes) {
                    $retry++;
                } else {
                    throw $e;
                }
            }
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

    protected function beforeBegin()
    {
        if (!isset($this->event[self::TIMING_BEFORE][self::ACTION_BEGIN])) {
            return;
        }
        foreach ($this->event[self::TIMING_BEFORE][self::ACTION_BEGIN] as $callback) {
            $callback->__invoke();
        }
    }

    public function begin()
    {
        $this->focusMaster = true;
        if (!$this->success[self::MASTER_RESOURCE_ID]) {
            $this->connect(self::MASTER_RESOURCE_ID);
        }
        $this->beforeBegin();
        $retry = 0;
        $reconnectLimitTimes = 1;
        while (true) {
            try {
                $return = $this->db[self::MASTER_RESOURCE_ID]->beginTransaction();
                break;
            } catch (\Conpoz\Core\Lib\Db\DBQuery\Exception $e) {
                echo $e->getCode();
                if ($e->getCode() != 2006) {
                    throw $e;
                }
                if ($retry < $reconnectLimitTimes) {
                    $retry ++;
                    $this->connect(self::MASTER_RESOURCE_ID);
                } else {
                    throw $e;
                }
            }
        }
        $this->afterBegin($return);
        return $return;
    }

    protected function afterBegin($success)
    {
        if (!isset($this->event[self::TIMING_AFTER][self::ACTION_BEGIN])) {
            return;
        }
        foreach ($this->event[self::TIMING_AFTER][self::ACTION_BEGIN] as $callback) {
            $callback->__invoke($success);
        }
    }

    protected function beforeCommit()
    {
        if (!isset($this->event[self::TIMING_BEFORE][self::ACTION_COMMIT])) {
            return;
        }
        foreach ($this->event[self::TIMING_BEFORE][self::ACTION_COMMIT] as $callback) {
            $callback->__invoke();
        }
    }

    public function commit()
    {
        if (!$this->success[self::MASTER_RESOURCE_ID]) {
            $this->connect(self::MASTER_RESOURCE_ID);
        }
        $this->beforeCommit();
        $return = $this->db[self::MASTER_RESOURCE_ID]->commit();
        $this->afterCommit($return);
        return $return;
    }

    protected function afterCommit($success)
    {
        if (!isset($this->event[self::TIMING_AFTER][self::ACTION_COMMIT])) {
            return;
        }
        foreach ($this->event[self::TIMING_AFTER][self::ACTION_COMMIT] as $callback) {
            $callback->__invoke($success);
        }
    }

    protected function beforeRollback()
    {
        if (!isset($this->event[self::TIMING_BEFORE][self::ACTION_ROLLBACK])) {
            return;
        }
        foreach ($this->event[self::TIMING_BEFORE][self::ACTION_ROLLBACK] as $callback) {
            $callback->__invoke();
        }
    }

    public function rollback()
    {
        if (!$this->success[self::MASTER_RESOURCE_ID]) {
            $this->connect(self::MASTER_RESOURCE_ID);
        }
        $this->beforeRollback();
        $return = $this->db[self::MASTER_RESOURCE_ID]->rollBack();
        $this->afterRollback($return);
        return $return;
    }

    protected function afterRollback($success)
    {
        if (!isset($this->event[self::TIMING_AFTER][self::ACTION_ROLLBACK])) {
            return;
        }
        foreach ($this->event[self::TIMING_AFTER][self::ACTION_ROLLBACK] as $callback) {
            $callback->__invoke($success);
        }
    }

    protected function beforeExecute()
    {
        if (!isset($this->event[self::TIMING_BEFORE][self::ACTION_EXECUTE])) {
            return;
        }
        foreach ($this->event[self::TIMING_BEFORE][self::ACTION_EXECUTE] as $callback) {
            $callback->__invoke();
        }
    }

    public function execute($sql, array $params = array(), $dbEnv = self::AUTO_RESOURCE_ID)
    {
        if (empty($sql)) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('Mysql sql statement is required');
        }

        $resourceIndex = null;
        if ($this->singleResoure) {
            $resourceIndex = self::MASTER_RESOURCE_ID;
        } else {
            switch ($dbEnv) {
                case self::MASTER_RESOURCE_ID:
                    $resourceIndex = self::MASTER_RESOURCE_ID;
                    $this->focusMaster = true;
                    break;
                case self::SLAVE_RESOURCE_ID:
                    $resourceIndex = self::SLAVE_RESOURCE_ID;
                    break;
                default:
                // case self::AUTO_RESOURCE_ID:
                    if ($this->focusMaster) {
                        $resourceIndex = self::MASTER_RESOURCE_ID;
                    } else {
                        //'IN SHARE MODE', 'FOR UPDATE'
                        if (stripos(trim($sql), 'SELECT') === 0 && !preg_match('/\s+lock\s+in\s+share\s+mode/i', $sql) && !preg_match('/\s+for\s+update/i', $sql)) {
                            $resourceIndex = self::SLAVE_RESOURCE_ID;
                        } else {
                            $resourceIndex = self::MASTER_RESOURCE_ID;
                            $this->focusMaster = true;
                        }
                    }
            }
        }

        $this->sth = null;
        if (!$this->success[$resourceIndex]) {
            $this->connect($resourceIndex);
        }
        $this->sth = $this->sthProcess($resourceIndex, $sql, $params);

        /**
        * execute and handle deadlock, and redo for setting times
        **/
        $this->beforeExecute();
        if ($this->db[$resourceIndex]->inTransaction()) {
            $success = $this->sth->execute();
        } else {
            $retry = 0;
            $reconnectLimitTimes = 1;
            while (true) {
                try {
                    $success = $this->sth->execute();
                    /**
                    * executed and no exception;
                    **/
                    break;
                } catch (\PDOException $e) {
                    if ($retry > $this->deadlockRetryTimes) {
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
                } catch (\Conpoz\Core\Lib\Db\DBQuery\Exception $e) {
                    if ($e->getCode() != 2006) {
                        throw $e;
                    }
                    if ($retry < $reconnectLimitTimes) {
                        $this->connect($resourceIndex);
                        $this->sth = $this->sthProcess($resourceIndex, $sql, $params);
                        $retry ++;
                    } else {
                        throw $e;
                    }
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
        $this->afterExecute($rh);
        return $rh;
    }

    protected function sthProcess ($resourceIndex, $sql, $params)
    {
        $sth = $this->db[$resourceIndex]->prepare($sql);
        foreach ($params as $k => $v) {
            $bindType = self::$bindType['others'];
            if (isset(self::$bindType[gettype($v)])) {
                $bindType = self::$bindType[gettype($v)];
            }
            if (is_int($k)) {
                $sth->bindValue($k + 1, $v, $bindType);
            } else {
                $sth->bindValue(':' . $k, $v, $bindType);
            }
        }
        return $sth;
    }

    protected function afterExecute($rh)
    {
        if (!isset($this->event[self::TIMING_AFTER][self::ACTION_EXECUTE])) {
            return;
        }
        foreach ($this->event[self::TIMING_AFTER][self::ACTION_EXECUTE] as $callback) {
            $callback->__invoke($rh);
        }
    }

    protected function beforeInsert()
    {
        if (!isset($this->event[self::TIMING_BEFORE][self::ACTION_INSERT])) {
            return;
        }
        foreach ($this->event[self::TIMING_BEFORE][self::ACTION_INSERT] as $callback) {
            $callback->__invoke();
        }
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
        $rh = $this->execute($sql, $this->data, self::MASTER_RESOURCE_ID);
        $rh->lastInsertId = $this->db[self::MASTER_RESOURCE_ID]->lastInsertId();
        $this->afterInsert($rh);
        return $rh;
    }

    protected function afterInsert ($rh)
    {
        if (!isset($this->event[self::TIMING_AFTER][self::ACTION_INSERT])) {
            return;
        }
        foreach ($this->event[self::TIMING_AFTER][self::ACTION_INSERT] as $callback) {
            $callback->__invoke($rh);
        }
    }

    protected function beforeUpdate()
    {
        if (!isset($this->event[self::TIMING_BEFORE][self::ACTION_UPDATE])) {
            return;
        }
        foreach ($this->event[self::TIMING_BEFORE][self::ACTION_UPDATE] as $callback) {
            $callback->__invoke();
        }
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
        $rh = $this->execute($sql, array_merge($paramsAry, $params), self::MASTER_RESOURCE_ID);
        $this->afterUpdate($rh);
        return $rh;
    }

    protected function afterUpdate($rh)
    {
        if (!isset($this->event[self::TIMING_AFTER][self::ACTION_UPDATE])) {
            return;
        }
        foreach ($this->event[self::TIMING_AFTER][self::ACTION_UPDATE] as $callback) {
            $callback->__invoke($rh);
        }
    }

    protected function beforeDelete ()
    {
        if (!isset($this->event[self::TIMING_BEFORE][self::ACTION_DELETE])) {
            return;
        }
        foreach ($this->event[self::TIMING_BEFORE][self::ACTION_DELETE] as $callback) {
            $callback->__invoke();
        }
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
        $this->beforeDelete();
        $sql = 'DELETE FROM ' . $table . ' WHERE ' . $conditions;
        $rh = $this->execute($sql, $params, self::MASTER_RESOURCE_ID);
        $this->afterDelete($rh);
        return $rh;
    }

    protected function afterDelete ($rh)
    {
        if (!isset($this->event[self::TIMING_AFTER][self::ACTION_DELETE])) {
            return;
        }
        foreach ($this->event[self::TIMING_AFTER][self::ACTION_DELETE] as $callback) {
            $callback->__invoke($rh);
        }
    }

    public function event ($timing, $action, $callback)
    {
        $this->event[$timing][$action][] = $callback;
    }

    public function whereInHelper (array $data = array())
    {
        $obj = new \StdClass();
        $obj->str = '';
        $obj->bindData = array();
        if (empty($data)) {
            throw new \Conpoz\Core\Lib\Db\DBQuery\Exception('whereInHelper get an empty array');
        }
        foreach ($data as $k => $v) {
            if ($k != 0) {
                $obj->str .= ',';
            }
            $obj->str .= ':wi_' . $k;
            $obj->bindData['wi_' . $k] = $v;
        }
        $obj->str = '(' . $obj->str . ')';
        return $obj;
    }
}
