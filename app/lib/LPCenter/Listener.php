<?php
namespace Conpoz\App\Lib\LPCenter;

class Listener 
{
    public $base,
        $listener,
        $socket;
    public $conn = array();
    public $header404 = '';
    public $header200 = '';
    public $keepAlive;
    public $hashKey;
    public $HEL = "\r\n";

    public function __destruct () 
    {
        foreach ($this->conn as &$c) $c = NULL;
    }

    public function __construct ($params) 
    {
        /**
        * $params['port'] = 50126
        */
        $params = array_merge(array('port' => '50126', 'hashKey' => null), $params);
        $this->hashKey = $params['hashKey'];
        
        $this->base = new \EventBase();
        if (!$this->base) {
            echo "Couldn't open event base";
            exit(1);
        }

        // Variant #1
        /*
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!socket_bind($this->socket, '0.0.0.0', $port)) {
            echo "Unable to bind socket\n";
            exit(1);
        }
        $this->listener = new EventListener($this->base,
            array($this, "acceptConnCallback"), $this->base,
            EventListener::OPT_CLOSE_ON_FREE | EventListener::OPT_REUSEABLE,
            -1, $this->socket);
         */

        // Variant #2
         $this->listener = new \EventListener($this->base,
             array($this, "acceptConnCallback"), $this->base,
             \EventListener::OPT_CLOSE_ON_FREE | \EventListener::OPT_REUSEABLE, -1,
             "0.0.0.0:" . $params['port']);

        if (!$this->listener) {
            echo "Couldn't create listener";
            exit(1);
        }

        $this->listener->setErrorCallback(array($this, "accept_error_cb"));
    }

    public function dispatch () 
    {
        $this->base->dispatch();
    }

    // This callback is invoked when there is data to read on $bev
    public function acceptConnCallback ($listener, $fd, $address, $ctx) 
    {
        // We got a new connection! Set up a bufferevent for it. */
        echo 'accept: ' . $fd . PHP_EOL;
        $base = $this->base;
        $this->conn[$fd] = new \Conpoz\App\Lib\LPCenter\ListenerConnection($base, $fd, $this);
        
    }

    public function accept_error_cb ($listener, $ctx) 
    {
        $base = $this->base;
        fprintf(STDERR, "Got an error %d (%s) on the listener. "
            ."Shutting down.\n",
            \EventUtil::getLastSocketErrno(),
            \EventUtil::getLastSocketError());
        $base->exit(NULL);
    }
}