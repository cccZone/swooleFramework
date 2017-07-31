<?php


namespace Kernel\Swoole\Event;


class Close implements Event
{
        public function doEvent($server, $fd)
        {
                echo "{$fd} closed\r\n";
        }

}