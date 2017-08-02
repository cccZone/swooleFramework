<?php


namespace Kernel\Core\Conf;


use Igorw\Silex\JsonConfigDriver;
use Igorw\Silex\PhpConfigDriver;
use Igorw\Silex\TomlConfigDriver;
use Igorw\Silex\YamlConfigDriver;
use Swoole\Mysql\Exception;

class Config
{
        protected static $instant = null;
        protected $driver = null;
        protected $paths = [ '../conf'];
        protected $type = 'php';
        protected $configs = [];
        public function __construct($paths = [], $type = 'php')
        {
                $this->paths = array_merge($this->paths, $paths);
                $this->type = $type;
        }

        public function setDriverType(string $type)
        {
                $this->type = $type;
        }

        public function setLoadPath(array $paths, bool $cover = false)
        {
                if($cover) {
                        $this->paths = $paths;
                }else{
                        $this->paths = array_merge($this->paths, $paths);
                }
        }

        public function init()
        {
                if($this->driver == null) {
                        $type = strtolower($this->type);
                        switch ($type) {
                                case 'php':
                                        $this->driver = new PhpConfigDriver();
                                        break;
                                case  'yaml':
                                        $this->driver = new YamlConfigDriver();
                                        break;
                                case 'json':
                                        $this->driver = new JsonConfigDriver();
                                        break;
                                case 'toml':
                                        $this->driver = new TomlConfigDriver();
                                        break;
                                default:
                                        throw new Exception('Config Driver not found', 1);
                        }
                }
        }

        public function load()
        {
                $this->init();
                foreach ($this->paths as $path) {
                        $iterator = new \GlobIterator($path.DIRECTORY_SEPARATOR.'*.'.$this->type, \FilesystemIterator::KEY_AS_FILENAME);
                        if($iterator->count()>0) {
                                foreach ($iterator as $item) {
                                        $this->configs = array_merge($this->driver->load($item->getPathname()));
                                }
                        }
                }
        }

        public function get(string $name)
        {
                return $this->configs[$name] ?? '';
        }

        public function __get($name)
        {
                return $this->get($name);
        }

}