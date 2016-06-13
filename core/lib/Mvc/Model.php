<?php 

namespace Conpoz\Lib\Mvc;

class Model
{
    public $bag = null;
    public $model = array();
    public function __construct($bag)
    {
        $this->bag = $bag;
    }

    public function __get($modelName) 
    {
        if (!isset($this->model[$modelName])) {
            $modelClass = '\\Conpoz\\Model\\' . $modelName;
            $this->model[$modelName] = new $modelClass();
            $this->model[$modelName]->bag = $this->bag;
        }
        return $this->model[$modelName];
    }

    public function load($modelName, $data = null)
    {
        if (!isset($this->model[$modelName])) {
            $modelClass = '\\Conpoz\\Model\\' . $modelName;
            $this->model[$modelName] = new $modelClass($data);
            $this->model[$modelName]->bag = $this->bag;
        }
        return $this->model[$modelName];
    }
}