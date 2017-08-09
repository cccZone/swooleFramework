<?php


namespace Kernel\Swoole\Event\Http;

use Kernel\Core;
use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;
use Kernel\Core\Cache\Type\Hash;

class Shutdown implements Event
{
        use EventTrait;
        protected $server;
        public function __construct(\swoole_http_server $server)
        {
                $this->server = $server;
        }

        public function doEvent()
        {
                $class = new Hash( Core::getInstant()->get('redis'));
                $class->select(5);
                $class->flushdb();
        }
}