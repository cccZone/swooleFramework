<?php


namespace Kernel;


use DI\ContainerBuilder;
use Swoole\Mysql\Exception;

class Core
{
        public static $core = null;
        protected $container;

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
                //        'tcp'           =>      \Kernel\Swoole\SwooleTcpServer::getInstance(),
                        'http'          =>      \Kernel\Swoole\SwooleHttpServer::getInstance(),
                //        'websocket'     =>      \Kernel\Swoole\SwooleWebsocketServer::getInstance(),
                        'conf'          =>      \Kernel\Core\Conf\Config::getInstance($confPath)
                ]);
                $this->container = $containerBuilder->build();
                //加载配置文件
                $this->get('conf')->load();
        }

        private function isOne()
        {
                if(self::$core !== null) {
                        throw new Exception('core is construct');
                }
                self::$core = $this;
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
}