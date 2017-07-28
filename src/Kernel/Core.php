<?php


namespace Kernel;


use DI\ContainerBuilder;

class Core
{
        protected $container;

        public function __construct(array $paths = [])
        {
                $this->autoload($paths);
                $containerBuilder = new ContainerBuilder('\Kernel\Core\Di\Container');
                $containerBuilder->addDefinitions([
                        'server'        =>      \Kernel\Swoole\SwooleTcpServer::getInstance(),
                        'conf'          =>      \Kernel\Core\Conf\Config::getInstance()
                ]);

                $this->container = $containerBuilder->build();
        }

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

        public function initLoad(array $maps = []) {
                if(empty($maps)) {
                        return ;
                }
                foreach ($maps as $className=>$params) {
                        $this->container->make($className, $params);
                }
        }

        public function get($name) {
                return $this->container->get($name);
        }
}