<?php 

namespace Conpoz\Lib\Db;
class DBQuery 
{
    public $db = null;
    public $sth = null;
    private $success = false;
    private $errorInfo = null;

    public function __construct($params) {
        
        if (empty($params['adapter']) || empty($params['dbname']) || empty($params['host']) || empty($params['username']) || empty($params['password'])) {
            throw new \Exception("db connected miss params");
            return false;
        }

        $params['charset'] = isset($params['charset']) ? $params['charset'] : 'utf8';
        $dsn = $params['adapter'] . ':dbname=' .$params['dbname'] . ';host=' . $params['host'] . ';charset=' . $params['charset'];
        $user = $params['username'];
        $password = $params['password'];
        try {
            
            $this->db = new \PDO($dsn, $user, $password, array(
                \PDO::ATTR_PERSISTENT => true
            ));
        }
        catch (\PDOException $e) {
            $this->errorInfo = $e->getMessage();
            return false;
        }
        $this->success = true;
    }

    public function success() {
        return $this->success;
    }

    public function lastError() {
        return $this->errorInfo;
    }

    public function begin() {
        if (!$this->success) {
            return false;
        }
        $this->db->beginTransaction();
        return $this;
    }

    public function commit() {
        if (!$this->success) {
            return false;
        }
        return $this->db->commit();
    }

    public function rollback() {
        if (!$this->success) {
            return false;
        }
        return $this->db->rollBack();
    }

    public function execute($sql, array $params = array()) {
        $this->sth = null;
        if (!$this->success || empty($sql)) {
            return false;
        }
        $this->sth = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $this->sth->bindValue(':' . $k, $v);
        }
        $success = $this->sth->execute();
        return new \Conpoz\Lib\Db\DBQuery\ResultHandler(array(
            'success' => $success,
            'sth' => $this->sth,
            'error' => $this->sth->errorInfo(),
            'rowCount' => $this->sth->rowCount()
        ));
    }

    public function insert($table, array $data = array()) {
        if (!$this->success || empty($table) || empty($data)) {
            return false;
        }
        $this->beforeInsert($table, $data);
        $columnsStr = implode(',', array_keys($data));
        $columnsStr = '(' . $columnsStr . ')';
        $valuesBindStr = '(:' . implode(',:', array_keys($data)) . ')';
        $sql = 'INSERT INTO ' . $table .' '. $columnsStr . ' VALUES ' . $valuesBindStr;
        $rh = $this->execute($sql, $data);
        $rh->lastInsertId = $this->db->lastInsertId();
        return $rh;
    }

    protected function beforeInsert(&$table, &$data) {
        //do nothing;
    }

    public function update($table,array $data, $conditions = '1', array $params = array()) {
        if (!$this->success || empty($data) || empty($table)) {
            return false;
        }
        $this->beforeUpdate($table, $data);
        $updateStr = '';
        $conditionsStr = '';
        $paramsAry = [];
        foreach ($data as $columnName => $columnValue) {
            $updateStr .= ' ' . $columnName . ' =  :d_' . $columnName . ',';
            $paramsAry['d_' . $columnName] = $columnValue;
        }
        $updateStr = trim($updateStr, ',');
        $sql = 'UPDATE ' . $table . ' SET ' . $updateStr . ' WHERE ' . $conditions;
        return $this->execute($sql, array_merge($paramsAry, $params));
    }

    protected function beforeUpdate(&$table, &$data) {
        //do nothing;
    }

    public function delete($table, $conditions = 1, array $params = array()) {
        if (!$this->success || empty($table)) {
            return false;
        }
        $sql = 'DELETE FROM ' . $table . ' WHERE ' . $conditions;
        return $this->execute($sql, $params);
    }
}