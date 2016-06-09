<?php 
namespace Conpoz\Controller;

class Index extends \stdClass
{
    public function indexAction () 
    {
        // $rh = $this->bag->dbquery->execute("SELECT * FROM questions WHERE 1");
        // $this->view->rh = $rh;
        $this->view->render('index/index');
    }
}