<?php 

namespace Conpoz\Core\Lib\Mvc;

class Model
{
    public $bag = null;
    public $model = array();
    public function __construct($app)
    {
        $this->app = $app;
        $this->bag = $app->bag;
    }

    public function __get($modelName) 
    {
        if (!isset($this->model[$modelName])) {
            $modelClass = '\\Conpoz\\App\\Model\\' . $modelName;
            $this->model[$modelName] = new $modelClass();
            $this->model[$modelName]->app = $this->app;
            $this->model[$modelName]->bag = $this->bag;
        }
        return $this->model[$modelName];
    }

    public function load($modelName, $data = null)
    {
        if (!isset($this->model[$modelName])) {
            $modelClass = '\\Conpoz\\App\\Model\\' . $modelName;
            $this->model[$modelName] = new $modelClass($data);
            $this->model[$modelName]->app = $this->app;
            $this->model[$modelName]->bag = $this->bag;
        }
        return $this->model[$modelName];
    }
}