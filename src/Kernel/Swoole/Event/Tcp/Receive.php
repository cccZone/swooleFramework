<?php


namespace Kernel\Swoole\Event\Tcp;

use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;

class Receive implements Event
{
        use EventTrait;
        public function doEvent($server, $fd, $from_id, $data)
        {
                $server->send($fd, 'Swoole receive: '.$data);
        //        $server->close($fd);
        }

}