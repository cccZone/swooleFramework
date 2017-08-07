<?php


namespace Kernel\Swoole\Event\Http;


use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;

class Request implements Event
{
        use EventTrait;
        /* @var  \swoole_http_server $server*/
        protected $server;
        protected $action = '';
        protected $actionParams = [];
        protected static $crawlerUrls = [];
        const ACTION_CRAWLER = 'crawler';
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
                //todo:
                //$data = ['action'=>self::ACTION_CRAWLER, 'url'=>'https://udn.com/news/index'];
                if(isset($data['action']) and isset($data['url']) and $data['action'] == self::ACTION_CRAWLER) {
                        $this->action = strtolower($data['action']);
                        $this->actionParams = $data;
                }
                $response->end(json_encode(empty($data)?['code'=>0]:$data));
                $this->doClosure();
        }

        public function setEventCall(\Closure $closure = null, array $params = [])
        {
                $this->params = $params;
                return $this;
        }

        public function doClosure()
        {
                $callback = function (){
                        switch ($this->action) {
                                case self::ACTION_CRAWLER:
                                        $this->_crawler();
                                        break;
                        }
                        if($this->callback != null) {
                                $this->callback($this->params);
                        }
                };

                call_user_func($callback);
                return $this;
        }

        private function _crawler()
        {
                $data = $this->actionParams;
                $url = parse_url($data['url']);
                $now = time();
                if(array_key_exists($url['host'], self::$crawlerUrls)) {
                        $addTime = self::$crawlerUrls[$url['host']];
                        if(date('d',$addTime) == date('d',$now)) {
                              return;
                        }
                        self::$crawlerUrls[$url['host']] = $now ;
                }
                self::$crawlerUrls[$url['host']] = $now;
                $this->server->task($this->actionParams);
        }
}