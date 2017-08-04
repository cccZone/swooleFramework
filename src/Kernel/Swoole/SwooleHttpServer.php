<?php


namespace Kernel\Swoole;


use Kernel\Core\Conf\Config;
use Kernel\Server;

class SwooleHttpServer implements Server
{
        const EVENT = [
                'request','packet','pipeMessage','task','finish'
        ];
        protected $server;

        public function __construct(Config $config)
        {
                $server = $config->get('server');
                if(empty($server)) {
                        throw new \Exception('config not found');
                }
                $this->server = new \swoole_http_server($server['host'], $server['port'], $server['mode'], $server['type']);

                $extend = $config->get('event')['namespace'];
                foreach (self::EVENT as $event) {
                        $class = $extend.'\\'.ucfirst($event);
                        /* @var \Kernel\Swoole\Event\Event $callback */
                        if(!class_exists($class)) {
                                $class = '\\Kernel\\Swoole\\Event\\Http\\'.ucfirst($event);
                        }
                        $callback = new $class($this->server);
                        $this->server->on($event, [$callback, 'doEvent']);
                }
                $this->server->set($config->get('swoole'));
        }

        public function start(\Closure $callback = null): Server
        {
                $callback();
                $this->server->start();
                return $this;
        }

        public function shutdown(\Closure $callback = null): Server
        {
                // TODO: Implement shutdown() method.
        }

        public function close($fd, $fromId = 0) : Server
        {
                $this->server->close($fd, $fromId = 0);
                return $this;
        }

}