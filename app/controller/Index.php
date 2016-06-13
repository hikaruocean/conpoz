<?php 
namespace Conpoz\App\Controller;

class Index extends \stdClass
{
    public function indexAction () 
    {
        var_dump($this->bag->req->getQuery(array('name', 'go', 'sex')));
        
        /**
         *  version 1
         *  query db in controller
         */
        // $rh = $this->bag->dbquery->execute("SELECT * FROM questions WHERE 1");
        
        /**
        * version 2
        * query db by call model (use magic function __get())
        */
        // $rh = $this->model->Questions->getListRh();
        
        /**
         *  version 3
         *  query db by call model (use loader function load($modelName, $contructData = null))
         *  this version can call model's contruct function
         */
        $qModel = $this->model->load('Questions');
        $rh = $qModel->getListRh();
        
        /**
         * render view
         */
        $this->view->addView('/htmlTemplate');
        $this->view->addView('/index/index');
        require($this->view->getView());
    }
}