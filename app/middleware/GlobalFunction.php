<?php
/**
* fake middleware to register global function
*/
namespace Conpoz\App\Middleware {
    class GlobalFunction
    {
        public static function run ($controller)
        {
            return true;
        }
    }
}

namespace {
    function e($output)
    {
        return \Conpoz\Core\Lib\Util\Tool::html($output);
    }
    function f(&$data, $key, $default = null)
    {
        return \Conpoz\Core\Lib\Util\Tool::force($data, $key, $default);
    }
    function ef(&$data, $key, $default = null)
    {
        $returnData = \Conpoz\Core\Lib\Util\Tool::force($data, $key, $default);
        return \Conpoz\Core\Lib\Util\Tool::html($returnData);
    }
    function buildQuery($uri, $newGetData = array())
    {
        return \Conpoz\Core\Lib\Util\Tool::buildQuery($uri, $newGetData);
    }
}
