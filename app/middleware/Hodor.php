<?php
namespace Conpoz\App\Middleware;

class Hodor
{
    public static function run ($controller)
    {
        ob_start();
        register_shutdown_function(function (\Conpoz\Core\Lib\Util\Container $bag) {
            $hodorSay = PHP_EOL;
            $hodorSay .= 'IP: ' . $bag->req->getClientIp() . PHP_EOL;
            $hodorSay .= 'Method: ' . $bag->req->getMethod() . PHP_EOL;
            $hodorSay .= 'URI: ' . $bag->req->getUri() . PHP_EOL;
            switch ($bag->req->getMethod()) {
                case 'POST':
                $hodorSay .= 'PARAMS: ' . http_build_query($_POST) . PHP_EOL;
            }
            $hodorSay .= 'OUTPUT: ' . ob_get_contents() . PHP_EOL;
            \Conpoz\Core\Lib\Util\SysLog::log($hodorSay, 'hodor');
            ob_flush();
            flush();
        }, $controller->bag);
        return true;
    }
}
