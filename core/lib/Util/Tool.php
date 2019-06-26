<?php
namespace Conpoz\Core\Lib\Util;

class Tool
{
    public static function force(&$data, $key = null, $default = null) {
        switch (gettype($data)) {
            case 'array':
                if (!is_array($key)) {
                    $return = isset($data[$key]) ? $data[$key] : $default;
                } else {
                    $return = array();
                    foreach($key as $k => $v) {
                        if (is_int($k)) {
                            $return[$v] = isset($data[$v]) ? $data[$v] : $default;
                        } else {
                            $return[$k] = isset($data[$k]) ? $data[$k] : $v;
                        }
                    }
                }
                break;
            case 'object':
                if (!is_array($key)) {
                    $return = isset($data->{$key}) ? $data->{$key} : $default;
                } else {
                    $return = array();
                    foreach($key as $k => $v) {
                        if (is_int($k)) {
                            $return[$v] = isset($data->{$v}) ? $data->{$v} : $default;
                        } else {
                            $return[$k] = isset($data->{$k}) ? $data->{$k} : $v;
                        }
                    }
                }
                break;
            default:
                throw new \Exception('Tool::force method data type error');
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
