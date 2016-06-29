<?php 
namespace Conpoz\Core\Lib\Util;

class Tool
{
    public static function force(&$data, $key = null, $default = '') {
        $return = $default;
        switch (gettype($data)) {
            case 'array':
                if (isset($data[$key])) {
                    $return = $data[$key];
                }
                break;
            case 'object':
                if (isset($data->{$key})) {
                    $return = $data->{$key};
                }
        }
        return $return;
    }

    public static function html($string) {
        return htmlspecialchars($string, ENT_QUOTES);
    }
}