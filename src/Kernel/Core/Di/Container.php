<?php


namespace Kernel\Core\Di;

use Swoole\Mysql\Exception;

class Container implements IContainer, \ArrayAccess
{
        protected $instances = [];
        protected $bounds = [];
        protected $aliases = [];

        public function __set($name, $class)
        {
              $this->bind($name, $class);
        }

        public function __get($name)
        {
                return $this->get($name);
        }

        /**
         * 自动绑定（Autowiring）自动解析（Automatic Resolution）
         *
         * @param $className
         * @return object
         * @throws \Exception
         */
        public function build($className)
        {
                if(is_string($className) and $this->offsetExists($className)) {
                        $class =  $this->offsetGet($className);

                        if(is_object($class)) {
                                return $class;
                        }
                }

                // 如果是匿名函数（Anonymous functions），也叫闭包函数（closures）
                if ($className instanceof \Closure) {
                        // 执行闭包函数，并将结果
                        return $this->getClosure($className, $this);
                }
                /** @var \ReflectionClass $reflector */
                $reflector = new \ReflectionClass($className);

                // 检查类是否可实例化, 排除抽象类abstract和对象接口interface
                if (!$reflector->isInstantiable()) {
                        throw new \Exception("Can't instantiate this.");
                }

                /** @var \ReflectionMethod $constructor 获取类的构造函数 */
                $constructor = $reflector->getConstructor();
                // 若无构造函数，直接实例化并返回
                if (is_null($constructor)) {
                        return new $className;
                }
                // 取构造函数参数,通过 ReflectionParameter 数组返回参数列表
                $parameters = $constructor->getParameters();
                // 递归解析构造函数的参数
                $dependencies = $this->getDependencies($parameters);
                // 创建一个类的新实例，给出的参数将传递到类的构造函数。
                $class =  $reflector->newInstanceArgs($dependencies);
                $this->aliases[$className] = $class;
                return $class;
        }

        public function bind(string $key, $concrete = null) : IContainer
        {
                unset($this->instances[$key], $this->aliases[$key]);

                if (is_null($concrete)) {
                        $concrete = $key;
                }

                if ($concrete instanceof \Closure) {
                        $concrete = $this->getClosure($key, $concrete);
                }

                $this->instances[$key] = $this->build($concrete);
                return $this;
        }

        public function alias(string $key, $class) : IContainer
        {
                if($class instanceof \Closure) {
                        $class = $this->getClosure($key, $class);
                }
                /*if(is_string($class)) {
                        $class = $this->build($class);
                }*/
                $this->aliases[$key] = $class;
                return $this;
        }


        protected function getClosure($key, $concrete)
        {
                return function () use ($key, $concrete) {
                        return $concrete($key);
                };
        }

        public function offsetExists($key)
        {
                return isset($this->instances[$key]) ? true : isset($this->aliases[$key]) ? true : false;
        }

        public function offsetGet($key)
        {
                if(!$this->offsetExists($key)) {
                        throw new ObjectNotFoundException($key. ' not found');
                }
                isset($this->instances[$key]) && $class =  $this->instances[$key];
                isset($this->aliases[$key]) && $class = $this->aliases[$key];
                /* @var \stdClass  $class */
                return $class;
        }

        public function offsetSet($key, $value)
        {
                $this->bind($key, $value instanceof \Closure ? $value : function () use ($value) {
                        return $value;
                });
        }

        public function offsetUnset($key)
        {
                unset($this->bounds[$key], $this->instances[$key], $this->aliases[$key]);
        }

        /**
         * @param array $parameters
         * @return array
         * @throws Exception
         */
        public function getDependencies($parameters)
        {
                $dependencies = [];

                /** @var \ReflectionParameter $parameter */
                foreach ($parameters as $parameter) {
                        /** @var \ReflectionClass $dependency */
                        $dependency = $parameter->getClass();
                        if (is_null($dependency)) {
                                // 是变量,有默认值则设置默认值
                                $dependencies[] = $this->resolveNonClass($parameter);
                        } else {
                                // 是一个类，递归解析
                                $dependencies[] = $this->build($dependency->name);
                        }
                }

                return $dependencies;
        }

        /**
         * @param \ReflectionParameter $parameter
         * @return mixed
         * @throws Exception
         */
        public function resolveNonClass($parameter)
        {
                // 有默认值则返回默认值
                if ($parameter->isDefaultValueAvailable()) {
                        return $parameter->getDefaultValue();
                }

                throw new Exception('I have no idea what to do here.');
        }

        public function get($id)
        {
                if(!class_exists($id)) {
                        throw new ObjectNotFoundException($id. ' not found');
                }
                if(isset($this->instances[$id])) {
                        return $this->instances[$id];
                }
                if(isset($this->aliases[$id])) {
                        if(is_string($this->aliases[$id])) {
                                $this->aliases[$id] = $this->build($this->aliases[$id]);
                        }
                        return $this->aliases[$id];
                }
                return $this->build($id);
        }

        public function has($id)
        {
                if(isset($this->instances[$id])) {
                        return true;
                }
                if(isset($this->aliases[$id])) {
                        return true;
                }
                return false;
        }

}