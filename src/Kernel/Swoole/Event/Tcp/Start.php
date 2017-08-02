<?php


namespace Kernel\Swoole\Event\Tcp;

use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;

class Start implements Event
{
        use EventTrait;
        public function doEvent()
        {
                echo "start\r\n";
                // TODO: Implement doEvent() method.
        }
}