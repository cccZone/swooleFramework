<?php


namespace Kernel\Swoole\Event\Http;


use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;

class Tick implements Event
{
        use EventTrait;
        /* @var  \swoole_http_server $server*/
        protected $server;
        public function __construct(\swoole_http_server $server)
        {
                $this->server = $server;
        }
        public function doEvent(\swoole_server $server, $inteval)
        {
                file_put_contents('test', "workerId: ".$server->worker_id."\r\n", FILE_APPEND);
        }
}