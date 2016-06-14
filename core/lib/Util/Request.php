<?php 
namespace Conpoz\Lib\Util;

class Request
{
    public function __construct()
    {
        $uri = isset($_GET['_url']) ? $_GET['_url'] : '';
        $uriAry = explode('/', $uri);
        array_shift($uriAry);
        foreach ($uriAry as &$param) {
            $paramAry = explode(':', $param, 2);
            if (isset($paramAry[1])) {
                $_GET[$paramAry[0]] = $paramAry[1];
            } else {
                array_push($_GET, $paramAry[0]);
            }
        }

        if (isset($_GET['_url'])) {
            unset($_GET['_url']);
        }
    }

    public function getPost($name, $defaultValue = null)
    {
        if (!is_array($name)) {
            return isset($_POST[$name]) ? $_POST[$name] : $defaultValue;
        }
        $returnAry = array();
        foreach($name as $v) {
            $returnAry[$v] = isset($_POST[$v]) ? $_POST[$v] : $defaultValue;
        }
        return $returnAry;
    }

    public function getQuery($name, $defaultValue = null)
    {
        if (!is_array($name)) {
            return isset($_GET[$name]) ? $_GET[$name] : $defaultValue;
        }
        $returnAry = array();
        foreach($name as $v) {
            $returnAry[$v] = isset($_GET[$v]) ? $_GET[$v] : $defaultValue;
        }
        return $returnAry;
    }

    public function getFile($name)
    {
        if (!isset($_FILES[$name]) || empty($_FILES[$name])) {
            /**
             * Return empty array
             */ 
            return array();
        }

        if (!is_array($_FILES[$name]['name'])) {
            $uploadFile = new \Conpoz\Lib\Util\UploadFile($_FILES[$name]);
            return array($uploadFile);
        } 
        
        $result = array();
        foreach ($_FILES[$name]['name'] as $k => $v) {
            $uploadFile = new \Conpoz\Lib\Util\UploadFile(array(
                'name' => $_FILES[$name]['name'][$k],
                'type' => $_FILES[$name]['type'][$k],
                'size' => $_FILES[$name]['size'][$k],
                'tmp_name' => $_FILES[$name]['tmp_name'][$k],
                'error' => $_FILES[$name]['error'][$k],
                ));
            array_push($result, $uploadFile);
        }
        return $result;
    }

    public function getRawContent()
    {
        return file_get_contents('php://input');
    }

    public function getMethod()
    {
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            return null;
        }
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public function isAjax()
    {
        return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] === "XMLHttpRequest";
    }
}