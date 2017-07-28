<?php


namespace Kernel\Swoole\Event;


class Close implements Event
{
        public function doEvent($fd)
        {
                echo "{$fd} closed";
        }

}