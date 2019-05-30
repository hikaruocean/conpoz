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

    public static function logException (\Throwable $e, $refData = '', $suffix = 'conpoz')
    {
        $logString = $e->getMessage() . PHP_EOL;
        if (!empty($refData)) {
            $logString .= 'reference data: ' . $refData . PHP_EOL;
        }
        $logString .= $e->getTraceAsString() . PHP_EOL;
        self::log($logString, $suffix);
    }

    public static function logRotate ($reserveDays = 30, $suffix = '*')
    {
        if ($suffix != '*' && !is_array($suffix)) {
            throw new \Exception('suffix must be * or string array');
        }
        if (is_array($suffix)) {
            foreach ($suffix as &$v) {
                $v .= '.log';
            }
            unset($v);
        }
        $lastkeepTime = strtotime(date('Y-m-d', strtotime('-' . $reserveDays . ' days')));
        $logFileAry = array();
        self::getLogFileArray(LOG_PATH, $suffix, $logFileAry);
        foreach ($logFileAry as $fileInfo) {
            if ($lastkeepTime > $fileInfo['time']) {
                echo 'delete: ' . $fileInfo['fullpath'] . PHP_EOL;
                unlink($fileInfo['fullpath']);
            }
        }
    }

    private static function getLogFileArray ($dirPath, $suffix, &$logFileAry)
    {
        if (is_dir($dirPath)) {
            if ($dh = opendir($dirPath)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        $fullpath = $dirPath . '/' . $file;
                        $fileType = filetype($fullpath);
                        if ($fileType == 'file') {
                            $fileInfo = explode('_', $file, 2);
                            if (count($fileInfo) != 2) {
                                continue;
                            }
                            if (!is_numeric($fileInfo[0])) {
                                continue;
                            }

                            if ($suffix == '*') {
                                array_push($logFileAry, array('time' => strtotime($fileInfo[0]), 'fullpath' => $fullpath));
                            } else { //array
                                if (!in_array($fileInfo[1], $suffix)) {
                                    continue;
                                }
                                array_push($logFileAry, array('time' => strtotime($fileInfo[0]), 'fullpath' => $fullpath));
                            }
                        } elseif ($fileType == 'dir') {
                            self::getLogFileArray($dirPath . '/' . $file, $suffix, $logFileAry);
                        }
                    }
                }
                closedir($dh);
            }
        }
        return $logFileAry;
    }
}
