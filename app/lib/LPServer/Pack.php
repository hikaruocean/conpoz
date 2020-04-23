<?php 
namespace Conpoz\App\Lib\LPServer;

class Pack 
{
    public $hashKey;
    
    public function __construct ($hashKey = null)
    {
        $this->hashKey = $hashKey;
    }
    
    public function payload ($channelAry = array(), $data = array())
    {
        $channel = json_encode($channelAry);
        $ts = time();
        $tk = md5($channel . $ts . $this->hashKey);
        $payload = 'data=' . urlencode(json_encode($data)) . '&channel=' . urlencode($channel) . '&tk=' . $tk . '&ts=' . $ts;
        return $payload;
    }
}