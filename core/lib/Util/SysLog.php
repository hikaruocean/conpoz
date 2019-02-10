<?php
namespace Conpoz\Core\Lib\Util;

class SysLog
{
    public static function log ($logString = '', $suffix = null)
    {
        if (!is_null($suffix)) {
            $suffix = '_' . $suffix;
        } else {
            $suffix = '';
        }
        $filename = LOG_PATH . '/' . date('Ymd') . '_conpoz' . $suffix . '.log';
        $logString = '[' . date('Y-m-d H:i:s') .'] ' . $logString . PHP_EOL;
        file_put_contents($filename, $logString);
    }

    public static function logException (\Exception $e, $suffix = null)
    {
        if (!is_null($suffix)) {
            $suffix = '_' . $suffix;
        } else {
            $suffix = '';
        }
        $logString = $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
        self::log($logString, $suffix);
    }
}
