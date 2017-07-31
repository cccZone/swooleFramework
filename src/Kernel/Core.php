<?php


namespace Kernel;


use DI\ContainerBuilder;
use Swoole\Mysql\Exception;

class Core
{
        public static $core = null;
        protected $container;
        protected $reflection;

        /**
         * 核心类构造
         * Core constructor.
         * @param array $paths
         * @param array $confPath
         */
        public function __construct(array $paths = [], array $confPath = [])
        {
                $this->isOne();
                $this->autoload($paths);
                $containerBuilder = new ContainerBuilder('\Kernel\Core\Di\Container');
                $containerBuilder->addDefinitions([
                        'config'          =>      \Kernel\Core\Conf\Config::getInstance($confPath),
                        //getInstance($this->container)
                 //       'tcp'           =>      \Kernel\Swoole\SwooleTcpServer::class,
                 //       'http'          =>      \Kernel\Swoole\SwooleHttpServer::getInstance(),
                //        'websocket'     =>      \Kernel\Swoole\SwooleWebsocketServer::getInstance(),
                ]);
                $this->container = $containerBuilder->build();
                $this->get('config')->load();
                $this->container->set('tcp', $this->reflection(\Kernel\Swoole\SwooleTcpServer::class, $this->container));
                //加载配置文件
        }

        private function isOne()
        {
                if(self::$core !== null) {
                        throw new Exception('core is construct');
                }
                self::$core = $this;
        }

        public function getInstant()
        {
                if(self::$core === null) {
                        throw new Exception('core is not construct');
                }
                return self::$core;
        }
        /**
         * 注册加载SRC下文件
         * @param array $paths
         */
        public function autoload(array $paths = [])
        {
                if(empty($paths)) {
                        return ;
                }
                spl_autoload_register(function(string $class) use ($paths) {
                        $file = DIRECTORY_SEPARATOR.str_replace('\\',DIRECTORY_SEPARATOR, $class).'.php';
                        foreach($paths as $path) {
                                if(is_file($path.$file)) {
                                        include($path.$file);
                                        return true;
                                }
                        }
                        return false;
                });
        }

        /**
         * 加载默认
         * @param array $maps
         */
        public function initLoad(array $maps = []) {
                if(empty($maps)) {
                        return ;
                }
                foreach ($maps as $className=>$params) {
                        $this->container->make($className, $params);
                }
        }

        /**
         * 获取指定对象
         * @param $name
         * @return mixed
         */
        public function get($name) {
                return $this->container->get($name);
        }

        public function reflection($className, $params = null)
        {
                if (class_exists($className)) {
                        $instance = null;
                        $reflection = new \ReflectionClass($className);
                        $hasInstance = $reflection->hasMethod('getInstance');
                        if ($hasInstance) {
                                $instance = $params !== null ? $className::getInstance($params) : $className::getInstance();

                        } else {
                                $construct = $reflection->hasMethod('__construct');
                                $instance = $construct ? $reflection->newInstanceArgs($params) : null;
                        }
                        if ($instance === null) {
                                throw new \Exception('can\'t new Instance by ' . $className);
                        }
                        return $instance;
                }
                throw new \Exception('class not fount with '.$className);
        }
}