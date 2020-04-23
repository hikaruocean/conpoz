<?php 
namespace Conpoz\App\Lib\LPServer;

class ListenerConnection 
{
    public $bev, $base, $listener, $channel = array(), $fd, $e, $closeFlag = true, $HEL;

    public function __destruct () 
    {
        echo 'fd ' . $this->fd . ' leave' . PHP_EOL;
        echo 'now connection number is: ' . count($this->listener->conn) . PHP_EOL;
    }

    public function __construct ($base, &$fd, &$e, &$listener) 
    {
        $this->listener = &$listener;
        $this->HEL = $listener->HEL;
        $this->fd = &$fd;
        $this->e = &$e;
        $this->base = $base;
        $this->bev = new \EventBufferEvent($base, $fd, \EventBufferEvent::OPT_CLOSE_ON_FREE);

        $this->bev->setCallbacks(array($this, "readCallback"), array($this, "writeCallback"),
            array($this, "eventCallback"), null);

        if (!$this->bev->enable(\Event::READ)) {
            echo "Failed to enable READ\n";
            return;
        }
    }

    public function readCallback ($bev, $ctx) 
    {
        // Copy all the data from the input buffer to the output buffer
        // Variant #1
        
        try {
            $reqObj = \Conpoz\App\Lib\LPParser\Http::parse($bev);
        } catch (\Exception $e) {
            $this->responseError($e->getMessage());
            return;
        }
        
        switch ($reqObj->pathInfo) {
            case '/send':
                echo 'send' . PHP_EOL;
                
                if (!isset($reqObj->queryParams['data']) || !isset($reqObj->queryParams['channel'])) {
                    $this->responseError('send action require data, channel');
                    return;
                }
                
                /**
                * 使用 hashKey
                */
                if (!is_null($this->listener->hashKey)) {
                    if (!isset($reqObj->queryParams['tk']) || !isset($reqObj->queryParams['ts'])) {
                        $this->responseError('send action require tk, ts');
                        return;
                    }
                    if (md5($reqObj->queryParams['channel'] . $reqObj->queryParams['ts'] . $this->listener->hashKey) !== $reqObj->queryParams['tk'] || (((int) $reqObj->queryParams['ts'] + 15) < time())) {
                        $this->responseError('send action tk failed');
                        return;
                    }
                }
                $smt = microtime(true);
                $sendData = json_decode($reqObj->queryParams['data'], true);
                $sendChannel = json_decode($reqObj->queryParams['channel'], true);
                if (!$sendData || !$sendChannel || !is_array($sendChannel)) {
                    $this->responseError('data, channel need be json format and channel must be array');
                    return;
                }
                
                /**
                * 傳送資料連線本身, 回應 request 端結果
                */
                $eb = new \EventBuffer();
                $payload = json_encode(array('result' => 0, 'smt' => $smt));
                $eb->add($this->listener->header200 . base_convert(strlen($payload), 10, 16) . $this->HEL . $payload . $this->HEL . '0' . $this->HEL . $this->HEL);
                $bev->output->addBuffer($eb);
                
                /**
                * 處理 cluster 通訊
                */
                if (!is_null($this->listener->centerHost)) {
                    $toUpstreamPayload = json_encode(array('action' => 'broadcast', 'channel' => $sendChannel, 'data' => $sendData)) . $this->HEL;
                    //send pack to upstream
                    echo 'send to center : ' . $toUpstreamPayload;
                    $retryCount = 0;
                    $eb = new \EventBuffer();
                    $eb->add($toUpstreamPayload);
                    while(($addResult = $this->listener->centerBev->output->addBuffer($eb)) === false && $retryCount < 3) {
                        $retryCount ++;
                        usleep(500);
                    }
                    if ($addResult === false) {
                        echo 'send to center failed' . $this->HEL;
                    } else {
                        echo 'send to center ok' . $this->HEL;
                    }
                }
                
                if (is_array($sendChannel)){
                    /**
                    * 指定 channel 傳送，就寫到該 channel 的 tempBuffer
                    */
                    echo 'specified channel add buffer' . PHP_EOL;
                    $sendChannelLog = '';
                    foreach ($sendChannel as $channelId) {
                        if (isset($this->listener->channel[$channelId]) && $this->listener->channel[$channelId]['lastWatchTime'] + 65 > time()) {
                            $sendChannelLog .= $channelId . ',';
                            $this->listener->channel[$channelId]['tempBuffer'][] = $sendData;
                        }
                    }
                    echo $sendChannelLog . PHP_EOL;
                }
                
                break;
            case '/read':
                /**
                * 指定 channelId
                */
                if (!isset($reqObj->queryParams['channel'])) {
                    $this->responseError('send action require channel');
                    return;
                }
                
                /**
                * 使用 hashKey
                */
                if (!is_null($this->listener->hashKey)) {
                    if (!isset($reqObj->queryParams['tk']) || !isset($reqObj->queryParams['ts'])) {
                        $this->responseError('read action require tk, ts');
                        return;
                    }
                    if (md5($reqObj->queryParams['channel'] . $reqObj->queryParams['ts'] . $this->listener->hashKey) !== $reqObj->queryParams['tk'] || (((int) $reqObj->queryParams['ts'] + 15) < time())) {
                        $this->responseError('read action tk failed');
                        return;
                    }
                }
                $this->channel = json_decode($reqObj->queryParams['channel'], true);
                if (!$this->channel || !is_array($this->channel)) {
                    $this->responseError('channel need be json format and channel need be array');
                    return;
                }
                if ($this->listener->keepAlive === true) {
                    $this->closeFlag = false;
                }
                foreach ($this->channel as $channelId) {
                    $this->listener->channel[$channelId]['conn'][$this->fd] = $this;
                    $this->listener->channel[$channelId]['lastWatchTime'] = time();
                }
                break;
            default:
                $eb = new \EventBuffer();
                $eb->add($this->listener->header404 . '0' . $this->HEL . $this->HEL);
                $bev->output->addBuffer($eb);
        }
    }
    
    public function writeCallback ($bev)
    {
        if (0 === $bev->output->length && $this->closeFlag) {
            $this->kill();
        }
    }

    public function eventCallback ($bev, $events, $ctx) 
    {
        if ($events & \EventBufferEvent::ERROR) {
            echo "ERROR" . PHP_EOL;
            echo \EventUtil::getLastSocketError() . PHP_EOL;
            $this->kill();
        }

        if ($events & (\EventBufferEvent::EOF)) {
            echo "EOF" . PHP_EOL;
            $this->kill();
        }
        
        if ($events & \EventBufferEvent::TIMEOUT) {
            echo 'timeout' . PHP_EOL;
            $this->kill();
        }
    }
    
    private function responseError ($err = 'null')
    {
        echo 'input data invalid message: [' . $err . ']' . PHP_EOL;
        $eb = new \EventBuffer();
        $payload = json_encode(array('result' => -2));
        $eb->add($this->listener->header200 . base_convert(strlen($payload), 10, 16) . $this->HEL . $payload . $this->HEL . '0' . $this->HEL . $this->HEL);
        $this->bev->output->addBuffer($eb);
        return;
    }
    
    public function kill ()
    {
        $this->bev->free();
        /**
        * $this->bev = null 是關鍵，若沒這樣做，判定有循環指向，不會呼叫 __destruct
        */
        $this->bev = null;
        $this->e->delTimer();
        $this->e = null;
        unset($this->listener->conn[$this->fd]);
        if (is_array($this->channel)) {
            foreach ($this->channel as $channelId) {
                unset($this->listener->channel[$channelId]['conn'][$this->fd]);
            }
        }
    }
}