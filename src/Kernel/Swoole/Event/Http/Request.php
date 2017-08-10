<?php


namespace Kernel\Swoole\Event\Http;

use Kernel\Core;
use Kernel\Core\Cache\Type\Hash;
use Kernel\Core\Cache\Redis;
use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;
use Library\Crawler\Crawler;

class Request implements Event
{
        use EventTrait;
        /* @var  \swoole_http_server $server*/

        protected $action = '';
        protected $actionParams = [];

        protected $server;
        protected $redis;


        const ACTION_CRAWLER = 'crawler';
        const ACTION_KILL = 'kill';
        const ACTION_RELOAD = 'reload';
        const ACTION_STOP = 'stop';
        const KEY = 'crawler:list:';
        const DAY_SECOND = 86400;
        const MIN_INTERVAL = 3600;
        const MAX_INTERVAL = self::DAY_SECOND;

        public function __construct(\swoole_http_server $server)
        {
                $this->server = $server;
        }

        public function doEvent(\swoole_http_request $request, \swoole_http_response $response)
        {
                if(isset($request->server['request_uri']) and $request->server['request_uri'] == '/favicon.ico') {
                        $response->end(json_encode(empty($data)?['code'=>0]:$data));
                        return;
                }
                $data = $request->rawContent();
                $data = json_decode($data, true);
                $data = $this->_check($data);
                $response->end(json_encode($data));
                $this->doClosure();
        }

        private function _check($data)
        {
//                if(isset($data['interval']) and $data['interval'] < self::MIN_INTERVAL and $data['interval'] > self::MAX_INTERVAL)
//                {
//                        return ['code'=>0,'response'=>'interval value error:'.$data['interval']];
//                }

                if(isset($data['action']) and isset($data['flag'])) {
                        switch ($data['action']) {
                                case self::ACTION_CRAWLER:
                                        return $this->_crawler($data);
                                case self::ACTION_KILL:
                                case self::ACTION_STOP:
                                        return $this->_stop($data);
                                case self::ACTION_RELOAD:
                                        return $this->_reload($data);
                                default:
                                        return ['code'=>0];
                        }
                }
                return ['code'=>0];
        }

        private function getHash(string $key) :Hash
        {
                $config = Core::getInstant()->get('config');
                $redis = new Redis($config, false);
                $class = new Hash($redis);
                //$class->select(5);
                $class->setKey($key);
                return $class;
        }

        private function _crawler(array $data)
        {
                $hash = $this->getHash(self::KEY.$data['flag']);
                if($hash->hasKey()) {
                        $cache = $hash->getAll();
                        if($cache['stop'] == 1) {
                                return $this->_start($data);
                        }
                        return $hash->getAll();
                }
                return $this->_start($data);
        }

        private function _stop(array $data)
        {
                $processId = -1;
                $hash = $this->getHash(self::KEY.$data['flag']);
                if($hash->hasKey()) {
                        $cache = $hash->getAll();
                        $processId = $cache['processId'] ?? '';
//                        $tickId = $cache['tickId'] ?? '';
//                        if (!empty($tickId)) {
//                                if($cache['workerId'] != $this->server->worker_id) {
//                                        $this->server->sendMessage(json_encode(["action" => "killTick", "tickId" => $tickId]), $cache['workerId']);
//                                }else {
//                                        echo "kill tick ".$tickId.PHP_EOL;
//                                        \swoole_timer_clear($tickId);
//                                }
//                        }
                        if (!empty($processId)) {
                                $hash->setField('stop', 1);
                                $this->_delProcess($processId);
                        }
                }
                unset($hash);
                return ['processId'=>$processId];
        }

        private function _reload(array $data)
        {
                $this->_stop($data);
                return $this->_start($data);
        }

//        private function _start(array $data) : array
//        {
//                $hash = $this->getHash(self::KEY.$data['flag']);
//                $processId = $this->_doCrawler($data);
//                if(isset($data['interval'])) {
//                        $tickId = $this->server->tick($data['interval']*1000,function ($tickId) use ($data){
//                                $hash = $this->getHash(self::KEY.$data['flag']);
//                                $cache = $hash->getAll();
//                                if(isset($cache['processId'])) {
//                                        echo "kill processId".$cache['processId'].PHP_EOL;
//                                        \swoole_process::kill($cache['processId']);
//                                        \swoole_process::wait(true);
//                                };
//                                if(isset($cache['tickId']) and $cache['tickId'] != $tickId) {
//                                        if($cache['workerId'] != $this->server->worker_id) {
//                                                $this->server->sendMessage(json_encode(["action" => "killTick", "tickId" => $tickId]));
//                                        }else {
//                                                \swoole_timer_clear($tickId);
//                                        }
//                                        $hash->setField('tickId', $tickId);
//                                        $hash->setField('workerId', $this->server->worker_id);
//                                }
//                                $hash->setField('processId', $this->_doCrawler($data));
//                        });
//                        $hash->setField('tickId',$tickId);
//                }
//                $hash->setField('workerId', $this->server->worker_id);
//                $hash->setField('processId', $processId);
//                unset($hash);
//                return ['processId'=>$processId,'tickId'=>$tickId??'','workerId'=>$this->server->worker_id];
//        }

        private function _doCrawler(array $data)
        {
                $process = new \swoole_process(function () use ($data){
                        $task = Crawler::getCrawler($data);
                        $task->run();
                });
                $processId =  $process->start();
                $process->name($data['flag']);
                return $processId;
        }

        private function _start(array $data)
        {
                $hash = $this->getHash(self::KEY.$data['flag']);
                $cache = $hash->getAll();
                echo "cache ".json_encode($cache).PHP_EOL;
                echo "data ".json_encode($data).PHP_EOL;
                if(isset($cache['stop'])) {
                        if($cache['stop'] == 0) {
                                $processId = $this->_doCrawler($data);
                                $hash->setField('processId', $processId);
                        }else{
                                $processId = '-1';
                        }
                }else{
                        $processId = $this->_doCrawler($data);
                        $hash->setField('processId', $processId);
                        $hash->setField('stop', 0);
                }

                $this->server->after($data['interval'] * 1000, function () use ($data) {
                        $hash = $this->getHash(self::KEY . $data['flag']);
                        $cache = $hash->getAll();
                        echo $this->server->worker_id.PHP_EOL;
                        if (isset($cache['processId']) and $cache['stop'] == '0') {
                                echo "{$data['flag']} stop : {$cache['stop']}" . PHP_EOL;
                                echo "reload " . $data['flag'] . PHP_EOL;
                                $this->_delProcess($cache['processId']);
                        }
                        //不同workerId定时器共用一个问题  包括swoole_timer_tick swoole_time_afer都有此问题
                        $this->_start($data);
                });

                return ['processId' => $processId ?? ($cache['processId'] ?? '-1')];
        }

        private function _delProcess($processId)
        {
                $this->server->tick(1000,function ($id) use($processId){
                        echo "kill processId".$processId.PHP_EOL;
                        \swoole_process::kill($processId);
                        \swoole_process::wait(true);
                        \swoole_timer_clear($id);
                });
        }
}