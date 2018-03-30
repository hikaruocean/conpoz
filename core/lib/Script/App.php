<?php

namespace Conpoz\Core\Lib\Script;

class App
{
    public $config;
    public $taskName;
    public $actionName;
    public function __construct($bag)
    {
        $this->bag = $bag;
    }

    public function run($config = null, &$argv)
    {
        /**
         * get task, get action
         * Gen taskObject
         */
        $this->config = $config;
        $this->argv = &$argv;
        if (!isset($argv[1]) || empty($argv[1])) {
            throw new \Exception('WE NEET TASK::ACTION PARAMETER' . PHP_EOL);
        }
        $routeInfo = explode('::', $argv[1]);
        if (count($routeInfo) != 2) {
            throw new \Exception('TASK::ACTION ERROR' . PHP_EOL);
        }
        $this->dispatch($routeInfo[0], $routeInfo[1]);
    }

    public function dispatch ($task, $action)
    {
        $task = ucfirst($task);
        if (!class_exists('Conpoz\\App\\Task\\' . $task)) {
            throw new \Exception('TASK NOT FOUND' . PHP_EOL);
        } else {
            $taskClass = '\\Conpoz\\App\\Task\\' . $task;
            $taskObject = new $taskClass();
            if (!is_callable(array($taskObject, $action . 'Action'))) {
                throw new \Exception('ACTION NOT FOUND' . PHP_EOL);
            }
        }

        /**
         * Gen Script structure
         */

        $this->taskName = $task;
        $this->actionName = $action;
        $this->task = $taskObject;
        $this->model = new \Conpoz\Core\Lib\Mvc\Model($this);
        $taskObject->app = $this;
        $taskObject->bag = $this->bag;
        $taskObject->model = $this->model;
        if (method_exists($taskObject, 'init')) {
            if ($taskObject->init($this->bag) === false) {
                return false;
            }
        }
        $taskObject->{$action . 'Action'}($this->bag);
    }
}
