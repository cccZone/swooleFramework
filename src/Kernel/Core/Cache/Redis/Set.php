<?php


namespace Kernel\Core\Cache\Redis;


use Kernel\Core\Cache\Redis;

class Set
{
        protected $_redis;
        protected $_key;
        public function __construct(Redis $redis)
        {
                $this->_redis = $redis;
        }

        public function get()
        {
               $value = $this->_redis->spop($this->_key);
               if(empty($value)) {
                       return '';
               }
               return $value;
        }

        public function addValue(string $value) : bool
        {
                return $this->_redis->sadd($this->_key, $value) > 0 ? true : false;
        }

	public function addValues(array $values) : bool
	{
		return $this->_redis->sadd($this->_key, $values) > 0 ? true : false;
	}

        public function getLength() : int
        {
                return intval($this->_redis->scard($this->_key));
        }

        public function diff(string $otherSet)
        {
                $result = [];
                $response =  $this->_redis->sdiff($this->_key, $otherSet);
                for($i=0,$num=count($response);$i<$num;$i++) {
                        $result[$response[$i]] = intval($response[++$i]);
                }
                return $result;
        }

        public function diffSave(string $newKey, string $oldKey) : bool
        {
                return $this->_redis->sdiffstore($newKey, $this->_key, $oldKey) > 0 ? true : false;
        }

        public function inter(Set $otherSet)
        {
                $result = [];
                $response =  $this->_redis->sinter($this->_key, $otherSet->getKey());
                for($i=0,$num=count($response);$i<$num;$i++) {
                        $result[$response[$i]] = intval($response[++$i]);
                }
                return $result;
        }

        public function interSave(string $newKey, string $oldKey) : bool
        {
                return $this->_redis->sinterstore($this->_key, $newKey, $oldKey) > 0 ? true : false;
        }

        public function getAll()
        {
                $response =  $this->_redis->smembers($this->_key);
                return !empty($response) ? $response : [];
        }

        public function delField(string $field) : bool
        {
                return $this->_redis->srem($this->_key, $field) > 0 ? true : false;
        }

        public function delFields(string $fields) : int
        {
                return intval($this->_redis->srem($this->_key, $fields));
        }

        public function union(string $key)
        {
                $result = [];
                $response =  $this->_redis->sunion($this->_key, $key);
                for($i=0,$num=count($response);$i<$num;$i++) {
                        $result[$response[$i]] = intval($response[++$i]);
                }
                return $result;
        }

        public function unionSave(string $newKey, string $oldKey) : bool
        {
                return $this->_redis->sunionstore($newKey, $this->_key,$oldKey) > 0 ? true : false;
        }

        public function __call($name, $arguments)
        {
                $this->_redis->$name($arguments);
        }
}