<?php 
namespace Conpoz\Core\Lib\Db\DBQuery;

class Exception extends \Exception
{
    public function __construct($message = null, $code = null, $errfile = null, $errline = null) {
        parent::__construct($message, $code);
        $this->file = $errfile;
        $this->line = $errline;
    }
}