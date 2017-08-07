<?php


namespace Kernel\Swoole\Event\Http;


use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;

class Close implements Event
{
        use EventTrait;
        public function doEvent()
        {

        }
}