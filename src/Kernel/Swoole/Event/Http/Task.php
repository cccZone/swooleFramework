<?php


namespace Kernel\Swoole\Event\Http;

use Kernel\Core;
use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;

class Task implements Event
{
        use EventTrait;
        /* @var  \swoole_http_server $server*/
        protected $server;
        public function __construct(\swoole_http_server $server)
        {
                $this->server = $server;
        }

        public function doEvent(\swoole_server $server, $task_id, $fromId, $data)
        {
                if(is_array($data) and isset($data['action'])) {
                        switch ($data['action']) {
                                case 'crawler':
                                        $urls = is_array($data['url']) ? $data['url'] : [$data['url']];
                                        Core::getInstant()->doCrawler(function (\Library\Crawler\Crawler $crawler) use($urls, $data){
                                                $crawler->initUrls($urls);
                                                $crawler->run();
                                                unset($crawler);
                                               // $this->server->finish($data);
                                        });
                                        break;
                        }
                }
                $this->doClosure();
        }
}