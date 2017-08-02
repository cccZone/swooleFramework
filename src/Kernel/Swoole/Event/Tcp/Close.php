<?php


namespace Kernel\Swoole\Event\Tcp;

use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;

class Close implements Event
{
        use EventTrait;
        public function doEvent($server, $fd)
        {
                echo "{$fd} closed\r\n";
        }

}