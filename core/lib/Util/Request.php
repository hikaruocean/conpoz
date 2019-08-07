<?php
namespace Conpoz\Core\Lib\Util;

class Request
{
    public $uri = null;

    public function __construct()
    {
        $this->uri = isset($_GET['_url']) ? rtrim($_GET['_url'], '/') : '';
        $uriAry = explode('/', $this->uri);
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
        foreach($name as $k => $v) {
            if (is_int($k)) {
                $returnAry[$v] = isset($_POST[$v]) ? $_POST[$v] : $defaultValue;
            } else {
                $returnAry[$k] = isset($_POST[$k]) ? $_POST[$k] : $v;
            }
        }
        return $returnAry;
    }

    public function getQuery($name, $defaultValue = null)
    {
        if (!is_array($name)) {
            return isset($_GET[$name]) ? $_GET[$name] : $defaultValue;
        }
        $returnAry = array();
        foreach($name as $k => $v) {
            if (is_int($k)) {
                $returnAry[$v] = isset($_GET[$v]) ? $_GET[$v] : $defaultValue;
            } else {
                $returnAry[$k] = isset($_GET[$k]) ? $_GET[$k] : $v;
            }
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
            $uploadFile = new \Conpoz\Core\Lib\Util\UploadFile($_FILES[$name]);
            return array($uploadFile);
        }

        $result = array();
        foreach ($_FILES[$name]['name'] as $k => $v) {
            $uploadFile = new \Conpoz\Core\Lib\Util\UploadFile(array(
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

    public function getRawHanlder()
    {
        $fh = fopen('php://input', 'rb');
        register_shutdown_function(function ($fh) {
            fclose($fh);
        }, $fh);
        return $fh;
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

    public static function getClientIp() {
        foreach (array(
                    'HTTP_CLIENT_IP',
                    'HTTP_X_FORWARDED_FOR',
                    'HTTP_X_FORWARDED',
                    'HTTP_X_CLUSTER_CLIENT_IP',
                    'HTTP_FORWARDED_FOR',
                    'HTTP_FORWARDED',
                    'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER)) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if ((bool) filter_var($ip, FILTER_VALIDATE_IP,
                                    FILTER_FLAG_IPV4 |
                                    FILTER_FLAG_NO_PRIV_RANGE |
                                    FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    public function getUri ()
    {
        return $this->uri;
    }
}
