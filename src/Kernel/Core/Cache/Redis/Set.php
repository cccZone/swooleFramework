<?php


namespace model\common\redis;


use Kernel\Core\Cache\Redis;

class Set
{
        protected $_redis;
        public function __construct(Redis $redis)
        {

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

        public function diff(Set $otherSet)
        {
                $result = [];
                $response =  $this->_redis->sdiff($this->_key, $otherSet->getKey());
                for($i=0,$num=count($response);$i<$num;$i++) {
                        $result[$response[$i]] = intval($response[++$i]);
                }
                return $result;
        }

        public function diffSave(Set $saveSet, Set $otherSet) : bool
        {
                return $this->_redis->sdiffstore($newKey, $this->_key, $otherSet->getKey()) > 0 ? true : false;
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

        public function interSave(Set $saveSet, Set $otherSet) : bool
        {
                return $this->_redis->sinterstore($saveSet->getKey(), $otherSet->getKey()) > 0 ? true : false;
        }

        public function getAll()
        {
                $result = [];
                $response =  $this->_redis->smembers($this->_key);
                for($i=0,$num=count($response);$i<$num;$i++) {
                        $result[$response[$i]] = intval($response[++$i]);
                }
                return $result;
        }

        public function delField(string $field) : bool
        {
                return $this->_redis->srem($this->_key, $field) > 0 ? true : false;
        }

        public function delFields(string $fields) : int
        {
                return intval($this->_redis->srem($this->_key, $fields));
        }

        public function union(Set $otherSet)
        {
                $result = [];
                $response =  $this->_redis->sunion($this->_key, $otherSet->getKey());
                for($i=0,$num=count($response);$i<$num;$i++) {
                        $result[$response[$i]] = intval($response[++$i]);
                }
                return $result;
        }

        public function unionSave(Set $saveSet, Set $otherSet) : bool
        {
                return $this->_redis->sunionstore($saveSet->getKey(), $this->_key, $otherSet->getKey()) > 0 ? true : false;
        }
}