<?php


namespace Kernel\Swoole;


use Kernel\Server;

class SwooleTcpServer implements Server
{
        const EVENT = [
                'close','connect','managerStart','receive','shutdown','start','workerStart','workerStop'
        ];
        protected $server;
        public static $instance = null;
        private function __construct($host = '0.0.0.0', $port = '9550', $mode = SWOOLE_PROCESS)
        {
                $this->server = new \swoole_server($host, $port, $mode, SWOOLE_SOCK_TCP);
                foreach (self::EVENT as $event) {
                        $class = '\\Kernel\\Swoole\\Event\\'.ucfirst($event);
                        $callback = new $class();
                        $this->server->on($event, [$callback, 'doEvent']);
                }

        }

        public static function getInstance($config = [])
        {
                if(self::$instance == null) {
                        if(empty($config)) {
                                self::$instance = new self();
                        }else{
                                self::$instance = new self($config['host'], $config['port'], $config['mode']);
                        }
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

        public function close($fd, $fromId = 0) : SwooleTcpServer
        {
                $this->server->close($fd, $fromId = 0);
                return $this;
        }

}