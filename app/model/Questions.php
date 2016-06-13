<?php 
namespace Conpoz\Model;

class Questions extends \stdClass
{
    public function getListRh()
    {
        $rh = $this->bag->dbquery->execute("SELECT * FROM questions WHERE 1");
        return $rh;
    }
}