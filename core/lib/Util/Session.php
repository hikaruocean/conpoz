<?php
namespace Conpoz\Core\Lib\Util;

class Session
{
    public static $autoCommit = true;
    public function __construct()
    {
        register_shutdown_function(function ($obj) {
            if ($obj->getAutoCommitStatus() === true) {
                $obj->commit();
            }
        }, $this);

        $this->start();
        $this->syncFromSession();
    }

    public function start()
    {
        session_start();
        return true;
    }

    public function begin ()
    {
        self::$autoCommit = false;
        return true;
    }

    public function rollback ()
    {
        if (self::$autoCommit === false) {
            $this->truncate();
            $this->syncFromSession();
            self::$autoCommit = true;
            return true;
        }
        return false;
    }

    public function commit ()
    {
        $_SESSION = get_object_vars($this);
        self::$autoCommit = true;
        return true;
    }

    public function truncate ()
    {
        foreach (get_object_vars($this) as $key => $val) {
            unset($this->{$key});
        }
        return true;
    }

    public function getAutoCommitStatus ()
    {
        return self::$autoCommit;
    }

    public function import ($data)
    {
        if (!is_array($data) && !is_object($data)) {
            return false;
        }
        $this->truncate();
        foreach ($data as $key => $val) {
            $this->{$key} = $val;
        }
        return true;
    }

    public function export ()
    {
        return get_object_vars($this);
    }
    
    private function syncFromSession ()
    {
        foreach ($_SESSION as $key => $val) {
            $this->{$key} = $val;
        }
    }
    
    public function __get ($key)
    {
        return null;
    }
}
