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

        public function doEvent(\swoole_server $server, $taskId, $fromId, $data)
        {
                $data = json_encode($data);
                if(is_array($data) and isset($data['action'])) {
                        switch ($data['action']) {
                                case 'crawler':
                                        $urls = is_array($data['url']) ? $data['url'] : [$data['url']];
                                        Core::getInstant()->doCrawler(function (\Library\Crawler\Crawler $crawler) use($urls, $data, $server, $taskId){
                                                $crawler->initUrls($urls);
                                                $server->tick(100000, function () use($taskId) {
                                                        $time = date('ymd his');
                                                        file_put_contents('task', "taskId: {$taskId}-{$time}\r\n", FILE_APPEND);
                                                });
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