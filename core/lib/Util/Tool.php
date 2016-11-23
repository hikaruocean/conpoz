<?php 
namespace Conpoz\Core\Lib\Util;

class Tool
{
    public static function force(&$data, $key = null, $default = '') {
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
}