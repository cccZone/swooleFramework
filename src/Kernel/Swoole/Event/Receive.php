<?php


namespace Kernel\Swoole\Event;


class Receive implements Event
{
        public function doEvent($server, $fd, $from_id, $data)
        {
                $server->send($fd, 'Swoole receive: '.$data);
        //        $server->close($fd);
        }

}