<?php


namespace Kernel\Swoole;


use Kernel\Core\Conf\Config;
use Kernel\Server;
use Psr\Container\ContainerInterface;

class SwooleTcpServer implements Server
{
        const EVENT = [
                'close','connect','managerStart','receive','shutdown','start','workerStart','workerStop'
        ];
        protected $server;
        public static $instance = null;
        public function __construct(Config $config)
        {
                $config = $config->get('server');
                if(empty($config)) {
                        throw new \Exception('config not found');
                }
                $this->server = new \swoole_server($config['host'], $config['port'], $config['mode'], $config['type']);
                foreach (self::EVENT as $event) {
                        $class = '\\Kernel\\Swoole\\Event\\Tcp\\'.ucfirst($event);
                        $class = new $class();
                        $this->server->on($event, [$class, 'doEvent']);
                }

        }

        public function start(\Closure $callback = null): Server
        {
             $this->server->start();
             return $this;
        }

        public function shutdown(\Closure $callback = null): Server
        {
                return $this;
        }

        public function close($fd, $fromId = 0) : Server
        {
                $this->server->close($fd, $fromId = 0);
                return $this;
        }

}