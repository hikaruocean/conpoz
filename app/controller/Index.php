<?php 
namespace Conpoz\Controller;

class Index extends \stdClass
{
    public function indexAction () {
        $rh = $this->bag->dbquery->execute("SELECT * FROM questions WHERE 1");
        while ($obj = $rh->fetch()) {
            var_dump($obj);
        }
        echo '<br />';
        echo 'hello conpoz!';
    }
}