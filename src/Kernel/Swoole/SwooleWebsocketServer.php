<?php


namespace Kernel\Swoole;


use Kernel\Server;
use Psr\Container\ContainerInterface;

class SwooleWebsocketServer implements Server
{
        const EVENT = [
                'close','connect','managerStart','receive','shutdown','start','workerStart','workerStop'
        ];
        protected $server;
        public static $instance = null;
        private function __construct(ContainerInterface $container)
        {
                $config = $container->get('config')->get('server');
                if(empty($config)) {
                        throw new \Exception('config not found');
                }
                $this->server = new \swoole_websocket_server($config['host'], $config['port'], $config['mode'], $config['type']);
                foreach (self::EVENT as $event) {
                        $class = '\\Kernel\\Swoole\\Event\\WebSocket\\'.ucfirst($event);
                        $callback = new $class;
                        $this->server->on($event, [$callback, 'doEvent']);
                }

        }

        public static function getInstance(ContainerInterface $container)
        {
                if(self::$instance == null) {
                        self::$instance = new self($container);
                }
                return self::$instance;
        }

        public function start(\Closure $callback = null): Server
        {
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