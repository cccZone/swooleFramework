<?php


namespace Kernel\Swoole\Event\Http;

use Kernel\Core;
use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;
use Library\Crawler\Crawler;
use Library\Crawler\Download\Udn as Download;
use Library\Crawler\Parse\Udn as Parse;
use Library\Crawler\Url\Udn as Url;

class Task implements Event
{
        use EventTrait;
        /* @var  \swoole_http_server $server*/
        protected $server;
        protected $db;
        protected $redis;
        protected $config;
        public function __construct(\swoole_http_server $server)
        {
                $this->server = $server;
                $core = Core::getInstant();
                $this->db = $core->get(Core\DB\DB::class);
                $this->redis = $core->get(Core\Cache\Redis::class);
                $this->config = $core->get(Core\Conf\Config::class);
        }

        public function doEvent(\swoole_server $server, $taskId, $fromId, $data)
        {
                //$data = json_encode($data);
                if(is_array($data) and isset($data['action'])) {
                        switch ($data['action']) {
                                case 'crawler':
                                        $urls = is_array($data['url']) ? $data['url'] : [$data['url']];
                                        $crawler = new Crawler(new Download(), new Parse(), new Url($this->config, $this->db, $this->redis));
                                        $crawler->initUrls($urls);
                                        $crawler->run();
                                        break;
                        }
                }
                $this->doClosure();
        }
}