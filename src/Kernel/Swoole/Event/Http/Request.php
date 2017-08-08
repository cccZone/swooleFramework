<?php


namespace Kernel\Swoole\Event\Http;


use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;

use model\common\redis\SortSet;

class Request implements Event
{
        use EventTrait;
        /* @var  \swoole_http_server $server*/
        protected $server;
        protected $action = '';
        protected $actionParams = [];
        const ACTION_CRAWLER = 'crawler';
        const ACTION_KILL = 'kill';
        const ACTION_RELOAD = 'reload';
        const ACTION_STOP = 'stop';

        protected $redis;
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
                $time = $this->_check($data);
                if(!is_array($data)) {
                        $data = ['code'=>0];
                }
                if(!empty($this->actionParams)) {
                        $data['worker'] = $time != 0 ? $time : 'false1';
                }else{
                        $data['worker'] = 'false';
                }
                $response->end(json_encode($data));
                $this->doClosure();
        }

        public function setEventCall(\Closure $closure = null, array $params = [])
        {
                $this->params = $params;
                return $this;
        }

        public function doClosure()
        {
                if($this->callback != null) {
                        $this->callback($this->params);
                }
                return $this;
        }

        private function _task($data)
        {
                $this->server->task($data);
        }

        private function _check($data)
        {
                $now = 0;
                if(isset($data['action'])) {
                        $now = time();
                        switch ($data['action']) {
                                case self::ACTION_CRAWLER:
                                        $this->_task($data);
                                        break;
                                case self::ACTION_KILL:
                                case self::ACTION_STOP:
                                        $this->_task($data);
                                        break;
                                case self::ACTION_RELOAD:
                                        $this->_task($data);
                                        break;
                                default:
                                        $now = 0;
                        }
                }
                return $now;
        }
}