<?php 
namespace Conpoz\Core\Lib\Util;

class Tool
{
    public static function force(&$data, $key = null, $default = null) {
        $return = $default;
        switch (gettype($data)) {
            case 'array':
                if (!is_array($key)) {
                    if (isset($data[$key])) {
                        $return = $data[$key];
                    }
                } else {
                    $return = array();
                    foreach($key as $v) {
                        $return[$v] = isset($data[$v]) ? $data[$v] : $default;
                    }
                }
                break;
            case 'object':
                if (!is_array($key)) {
                    if (isset($data->{$key})) {
                        $return = $data->{$key};
                    }
                } else {
                    $return = array();
                    foreach($key as $v) {
                        $return[$v] = isset($data->{$v}) ? $data->{$v} : $default;
                    }
                }
        }
        return $return;
    }

    public static function html($string) {
        return htmlspecialchars($string, ENT_QUOTES);
    }
    
    public static function buildQuery ($uri, $newGetData = array()) 
    {
        $getData = $_GET;
        $returnQueryStr = '';
        $uriInfoAry = explode('/', ltrim($uri, '/'));
        $noKeyParamsCount = 0;
        foreach ($uriInfoAry as $param) {
            $dataAry = explode(':', $param, 2);
            if (isset($dataAry[1])) {
                if (isset($newGetData[$dataAry[0]])) {
                    $value = $newGetData[$dataAry[0]];
                    unset($newGetData[$dataAry[0]]);
                } else {
                    $value = $dataAry[1];
                }
                unset($getData[$dataAry[0]]);
                $returnQueryStr .= '/' . $dataAry[0] . ':' . urlencode($value);
            } else {
                $returnQueryStr .= '/' . $dataAry[0];
                unset($getData[$noKeyParamsCount ++]);
            }
        }
        foreach ($getData as $k => $v) {
            $returnQueryStr .= '/' . $k . ':' . urlencode($v);
        }
        foreach ($newGetData as $k => $v) {
            $returnQueryStr .= '/' . $k . ':' . urlencode($v);
        }
        return $returnQueryStr;
    }
}
