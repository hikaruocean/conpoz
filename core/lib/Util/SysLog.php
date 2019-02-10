<?php
namespace Conpoz\Core\Lib\Util;

class SysLog
{
    public static $fileHandlerArray = array();

    public static function openLogFile ($suffix)
    {
        $filename = LOG_PATH . '/' . date('Ymd') . '_' . $suffix . '.log';
        self::$fileHandlerArray[$suffix] = fopen($filename, 'a');
        register_shutdown_function(function () use ($suffix) {
            fclose(\Conpoz\Core\Lib\Util\SysLog::$fileHandlerArray[$suffix]);
        });
    }

    public static function log ($logString = '', $suffix = 'conpoz')
    {
        if (!isset(self::$fileHandlerArray[$suffix])) {
            self::openLogFile($suffix);
        }
        $logString = '[' . date('Y-m-d H:i:s') .'] ' . $logString . PHP_EOL;
        fwrite(self::$fileHandlerArray[$suffix], $logString);
    }

    public static function logException (\Exception $e, $refData = '', $suffix = 'conpoz')
    {
        $logString = $e->getMessage() . PHP_EOL;
        if (!empty($refData)) {
            $logString .= 'reference data: ' . $refData . PHP_EOL;
        }
        $logString .= $e->getTraceAsString() . PHP_EOL;
        self::log($logString, $suffix);
    }
}
