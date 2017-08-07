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
                $data = $request->rawContent();
                $data = json_decode($data, true);
                //todo:
                //$data = ['action'=>self::ACTION_CRAWLER, 'url'=>'https://udn.com/news/index'];
                if(isset($data['action']) and isset($data['url']) and $data['action'] == self::ACTION_CRAWLER) {
                        $url = parse_url($data['url']);
                        if(!isset($url['host'])) {
                                $response->end(json_encode(['code'=>1,'url'=>$data['url']]));
                                return ;
                        }
                        if(array_key_exists($url['host'], self::$crawlerUrls)) {
                                $addTime = self::$crawlerUrls[$url['host']];
                                $now = time();
                                if(date('d',$addTime) == date('d',$now)) {
                                        $response->end(json_encode(['code'=>1,'url'=>$data['url'],'dayExists'=> $now]));
                                        return ;
                                }
                                self::$crawlerUrls[$url['host']] = $now ;
                        }
                        $this->action = strtolower($data['action']);
                        $this->actionParams = $data;
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
                $callback = function (){
                        switch ($this->action) {
                                case self::ACTION_CRAWLER:
                                        echo "task:".json_encode($this->actionParams)."\r\n";
                                        $this->server->task($this->actionParams);
                                        break;
                        }
                        if($this->callback != null) {
                                $this->callback($this->params);
                        }
                };

                call_user_func($callback);
                return $this;
        }
}