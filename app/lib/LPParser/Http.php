<?php 
namespace Conpoz\App\Lib\LPParser;

class Http 
{
    public static function parse (\EventBufferEvent &$bev)
    {
        if (is_null($line = $bev->input->readLine(\EventBuffer::EOL_CRLF))) {
            throw new \Exception('Payload Empty!');
        }
        $firstLineInfo = explode(' ', $line);
        if (count($firstLineInfo) !== 3) {
            throw new \Exception('Http Header Error');
        }
        
        $returnObj = new \stdClass();
        
        $returnObj->dataParams = array();
        $pathInfoAry = explode('?', $firstLineInfo[1], 2);
        $returnObj->pathInfo = trim($pathInfoAry[0]);
        $returnObj->queryParams = array();
        if (isset($pathInfoAry[1])) {
            parse_str($pathInfoAry[1], $returnObj->queryParams);
        }
                
        switch ($firstLineInfo[0]) {
            case 'GET':
                $returnObj->header = self::parseHeader($bev);
                break;
            case 'POST':
                $returnObj->header = self::parseHeader($bev);
                break;
            default:
                throw new \Exception($firstLineInfo[0] . ' Method Doesn\'t Suppert');
        }
        
        return $returnObj;
    }
    
    public static function parseHeader (\EventBufferEvent &$bev)
    {
        $header = array();
        while (!is_null($line = $bev->input->readLine(\EventBuffer::EOL_CRLF))) {
            $tempData = explode(': ', $line, 2);
            if (count($tempData) == 2) {
                $header[$tempData[0]] = $tempData[1];
            } else {
                $header[$tempData[0]] = null;
            }
        }
        return $header;
    }
}