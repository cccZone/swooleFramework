<?php


namespace Kernel\Swoole\Event\Http;


use Kernel\Swoole\Event\Event;
use Kernel\Swoole\Event\EventTrait;

class Request implements Event
{
        use EventTrait;

        public function doEvent($request, $response)
        {
                $this->doClosure();
                $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
        }
}