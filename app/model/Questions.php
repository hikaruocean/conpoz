<?php
namespace Conpoz\App\Model;

class Questions
{
    public function getListRh()
    {
        $rh = $this->bag->dbquery->execute("SELECT * FROM questions WHERE 1");
        return $rh;
    }
}
