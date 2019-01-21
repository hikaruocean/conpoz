<?php
namespace Conpoz\Core\Lib\Util;

class Worker
{
    public $params = array(
                'usleepTime' => 0,
                'usleepBaseTime' => 1000000,
                'usleepMaxTime' => 1000000 * 64,
                'usleepMinTime' => 15625,
                'childProcessQuantity' => 4,
                'detectLiveTimeSec' => 180,
            );
    public $jobObj = null;
    public $dbquery = null;
    public $queueName = 'job_queue';

    public function __construct ($dbquery = null, $jobObj = null, $params = array())
    {
        $this->params = array_merge($this->params, $params);
        $this->params['usleepTime'] = $this->params['usleepBaseTime'];
        $this->jobObj = $jobObj;
        $this->dbquery = $dbquery;
    }

    public function setJobObject ($jobObj = null)
    {
        $this->jobObj = $jobObj;
        return $this;
    }

    public function setParams ($params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function setDBQuery ($dbquery)
    {
        $this->dbquery = $dbquery;
        return $this;
    }

    public function run ()
    {
        if (!is_object($this->jobObj)) {
            throw new \Exception("setJobObject is required");
        }
        if (!is_object($this->dbquery)) {
            throw new \Exception("setDBQuery is required");
        }
        for ($i = 0 ; $i < $this->params['childProcessQuantity'] ; $i++) {
            $tempSockAry = array();
            if (socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $tempSockAry) === false) {
                $errStr = "socket_create_pair() failed. Reason: ".socket_strerror(socket_last_error());
                throw new \Exception($errStr);
            }
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new \Exception("could not fork process");
            } else if ($pid == 0) {
                $this->parentSock = $tempSockAry[1];
                unset($tempSockAry);
                /**
                * child escape loop
                */
                break;
            } else {
                $this->childSockAry[$pid] = $tempSockAry[0];
                $this->childLastLive[$pid] = time();
                unset($tempSockAry);
            }
        }
        /**
        * child job
        */
        if ($pid == 0) {
            $this->childLoop();
        } else {
            $this->managerLoop();
        }
    }

    private function managerLoop ()
    {
        $childPidAry = array_keys($this->childLastLive);
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, function () use ($childPidAry) {
            echo 'SIGTERM' . PHP_EOL;
            foreach ($childPidAry as $pid) {
                echo 'kill ' . $pid . PHP_EOL;
                posix_kill($pid, SIGKILL);
            }
            exit();
        });
        pcntl_signal(SIGINT, function () use ($childPidAry) {
            echo 'SIGINT' . PHP_EOL;
            foreach ($childPidAry as $pid) {
                echo 'kill ' . $pid . PHP_EOL;
                posix_kill($pid, SIGKILL);
            }
            exit();
        });
        pcntl_signal(SIGQUIT, function () use ($childPidAry) {
            echo 'SIGQUIT' . PHP_EOL;
            foreach ($childPidAry as $pid) {
                echo 'kill ' . $pid . PHP_EOL;
                posix_kill($pid, SIGKILL);
            }
            exit();
        });
        while (1) {
            $changedSockAry = $this->childSockAry;
            $write = NULL;
            $except = NULL;
            $changeSockNums = socket_select($changedSockAry, $write, $except, 1);
            if ($changeSockNums > 0) {

                foreach ($changedSockAry as $socket) {
                    $buf = socket_read($socket, 1024);
                    $index = array_search($socket, $this->childSockAry);
                    if (strlen($buf) === 0) {
                        echo "client disconnect [AutoCallExitCommand]" . PHP_EOL;
                        echo "exit command disconnect" . PHP_EOL;
                        unset($this->childSockAry[$index]);
                        unset($this->childLastLive[$index]);
                        socket_close($socket);
                    }
                    else {
                        $this->childLastLive[$index] = time();
                        echo 'PID ' . $index . ' Says : ' . $buf . '[' . date('YmdHis') . ']' . PHP_EOL;
                        $okStr = 'ok';
                        $this->speak($socket, $okStr);
                    }
                }
            }
            $curTime = time();
            foreach ($this->childLastLive as $index => $time) {
                $deltaTIme = $curTime - $time;
                if ($deltaTIme > $this->params['detectLiveTimeSec']) {
                    echo 'PID ' . $index . ' No Response ' . $deltaTIme . PHP_EOL;
                    $this->childLastLive[$index] = time();
                }
            }
        }
    }

    private function childLoop ()
    {
        try {
            $pid = getmypid();
            $dbquery = $this->dbquery;
            while (1) {
                $sql = "SELECT job_queue_id, name, params FROM " . $this->queueName . " WHERE status = 0 ORDER BY job_queue_id ASC LIMIT 1 FOR UPDATE";
                $dbquery->begin();
                $rh = $dbquery->execute($sql);
                $jobObj = $rh->fetch();
                if (!$jobObj) {
                    $dbquery->commit();
                    $this->params['usleepTime'] *= 2;
                    $this->params['usleepTime'] = $this->params['usleepTime'] > $this->params['usleepMaxTime'] ? $this->params['usleepMaxTime'] : $this->params['usleepTime'];
                } else {
                    $dbquery->update($this->queueName, array('status' => 1), "job_queue_id = :jobQueueId", array('jobQueueId' => $jobObj->job_queue_id));
                    $dbquery->commit();
                    if ($this->params['usleepTime'] > $this->params['usleepBaseTime']) {
                        $this->params['usleepTime'] = $this->params['usleepBaseTime'];
                    } else {
                        $this->params['usleepTime'] /= 2;
                        $this->params['usleepTime'] = $this->params['usleepTime'] < $this->params['usleepMinTime'] ? $this->params['usleepMinTime'] : $this->params['usleepTime'];
                    }
                    try {
                        if (!is_callable(array($this->jobObj, $jobObj->name))) {
                            throw new \Exception('job name not found');
                        }
                        $this->jobObj->{$jobObj->name}($jobObj->params);
                        $dbquery->update($this->queueName, array('status' => 2), "job_queue_id = :jobQueueId", array('jobQueueId' => $jobObj->job_queue_id));
                        $this->speakThenListen($this->parentSock, '[' . $jobObj->name . '] Success.');
                    } catch (\Exception $e) {
                        $dbquery->update($this->queueName, array('status' => -1), "job_queue_id = :jobQueueId", array('jobQueueId' => $jobObj->job_queue_id));
                        $this->speakThenListen($this->parentSock, '[' . $jobObj->name . '] Failed.' . PHP_EOL . $e->getMessage());
                    } catch (\Error $e) {
                        $dbquery->update($this->queueName, array('status' => -1), "job_queue_id = :jobQueueId", array('jobQueueId' => $jobObj->job_queue_id));
                        $this->speakThenListen($this->parentSock, '[' . $jobObj->name . '] Failed.' . PHP_EOL . $e->getMessage());
                    }
                }
                $this->speakThenListen($this->parentSock, $this->params['usleepTime']);
                usleep($this->params['usleepTime']);
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    public function speakThenListen ($sock, $notifyStr)
    {
        $this->speak($sock, $notifyStr);
        $buf = socket_read($sock, 256);
        unset($buf);
    }

    public function speak ($sock, $notifyStr)
    {
        if (socket_write($sock, $notifyStr, strlen($notifyStr)) === false) {
            $errStr = "child socket_write() failed. Reason: ".socket_strerror(socket_last_error($sock));
            throw new \Exception($errStr);
        }
    }
}
