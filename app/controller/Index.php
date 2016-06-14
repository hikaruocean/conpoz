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

    public function uploadAction () {
        if ($this->bag->req->getMethod() == 'POST') {
            $fileObjAry = $this->bag->req->getFile('image');

            if (empty($fileObjAry[0]->name)) {
                return;
            }
            $image = $this->bag->imageLoader->make($fileObjAry[0]->tmpName)->orientate();
            $image->widen(600);
            echo $image->response();
        } else {
            $this->view->addView('/htmlTemplate');
            $this->view->addView('/index/upload');
            require($this->view->getView());
        }
    }
}