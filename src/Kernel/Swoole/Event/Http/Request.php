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
        const ACTION_CRAWLER = 'crawler';
        const ACTION_KILL = 'kill';
        const ACTION_RELOAD = 'reload';
        const ACTION_STOP = 'stop';
        protected $server;
        protected $redis;
        const KEY = 'crawler:list:';
        static $hash = null;
        public function __construct(\swoole_http_server $server)
        {
                $this->server = $server;
                $this->redis = Core::getInstant()->get('redis');
        }

        public function doEvent(\swoole_http_request $request, \swoole_http_response $response)
        {
                if(isset($request->server['request_uri']) and $request->server['request_uri'] == '/favicon.ico') {
                        $response->end(json_encode(empty($data)?['code'=>0]:$data));
                        return;
                }
                $data = $request->rawContent();
                $data = json_decode($data, true);
//                $this->setEventCall(function () use ($data){
//                        if(isset($data['flag'])) {
//                                $this->_check($data);
//                        }
//                });
                if(isset($data['flag'])) {
                        $data = $this->_check($data);
                }
                $response->end(json_encode($data));
                $this->doClosure();
        }

        private function _check($data)
        {
                if(isset($data['action'])) {
                        switch ($data['action']) {
                                case self::ACTION_CRAWLER:
                                        return $this->_crawler($data);
                                case self::ACTION_KILL:
                                case self::ACTION_STOP:
                                        return $this->_stop($data);
                                case self::ACTION_RELOAD:
                                        return $this->_reload($data);
                                default:
                                        return [];
                        }
                }
        }

        private function getHash(string $key, Redis $redis) :Hash
        {
                if(self::$hash == null) {
                        $class = new Hash($redis);
                        $class->setKey($key);
                        $class->select(5);
                        self::$hash = $class;
                }
                return self::$hash;
        }

        private function _crawler(array $data)
        {
                $hash = $this->getHash(self::KEY.$data['flag'], $this->redis);
                if($hash->hasKey()) {
                        //todo:测试的
                        $processId = $this->_start($data);
                        if(isset($data['interval'])) {
                                $tickId = $this->server->tick($data['interval']*1000,function ($tickId) use ($data,$hash){
                                        echo "timer\r\n";
                                        $this->_reload($data, $tickId);
                                });
                                $hash->setField('tickId',$tickId);
                        }
                         $return = $hash->getAll();
                }else{
                        $processId = $this->_start($data);
                        if(isset($data['interval'])) {
                                $tickId = $this->server->tick($data['interval']*1000,function ($tickId) use ($data,$hash){
                                        echo "timer\r\n";
                                        $this->_reload($data, $tickId);
                                });
                                $hash->setField('tickId',$tickId);
                        }
                        $return = ['processId'=>$processId,'tickId'=>$tickId??''];
                }

                return $return;
        }

        private function _stop(array $data, string $nowTickId = '')
        {
                $processId = -1;
                $hash = $this->getHash(self::KEY.$data['flag'], $this->redis);
                if($hash->hasKey()) {
                        $value = $hash->getAll();
                        $processId = $value['processId'] ?? '';
                        $tickId = $value['tickId'] ?? '';
                        if (!empty($tickId) and !empty($nowTickId) and $tickId!=$nowTickId) {
                                echo "kill tick".$tickId.PHP_EOL;
                                \swoole_timer_clear($tickId);
                        }
                        if (!empty($processId)) {
                                echo "kill processId".$processId.PHP_EOL;
                                \swoole_process::kill($processId);
                                \swoole_process::wait(true);
                        }
                        $hash->delKey();
                }
                unset($hash);
                return ['processId'=>$processId,'tickId'=>$tickId??$nowTickId];
        }

        private function _reload(array $data, string $tickId = '')
        {
                $hash = $this->getHash(self::KEY.$data['flag'], $this->redis);
                $this->_stop($data, $tickId);
                $processId = $this->_doCrawler($data);
                $hash->setField('processId', $processId);
                if(!empty($tickId)) {
                        $hash->setField('tickId', $tickId);
                }
                unset($hash);
                return ['processId'=>$processId,'tickId'=>$tickId];
        }

        private function _start(array $data) : int
        {
                $hash = $this->getHash(self::KEY.$data['flag'], $this->redis);
                $processId = $this->_doCrawler($data);
                $hash->setField('processId', $processId);
                unset($hash);
                return $processId;
        }

        private function _doCrawler(array $data)
        {
                $process = new \swoole_process(function () use ($data){
                        $task = Crawler::getCrawler($data);
                        $task->run();
                });
                return $process->start();
        }
}