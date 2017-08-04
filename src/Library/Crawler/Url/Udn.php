<?php


namespace Library\Crawler\Url;


use Kernel\Core\Cache\Redis;
use Kernel\Core\Conf\Config;
use Kernel\Core\DB\DB;
use Kernel\Core\Cache\Redis\Set;

class Udn
{
        protected $urls = [];
        protected $got = [];
        protected $db;
        protected $cache;
        public function __construct(Config $config, DB $db, Redis $redis)
        {
                $this->db = $db;
                $this->urls = $this->getSet('crawler:'.date('ymd').':urls', $redis);
                $this->got = $this->getSet('crawler:'.date('ymd').':got', $redis);
        }

        public function addUrls(array $urls)
        {
                $got = $this->got->getAll();
                $diff = array_diff($urls, $got);
                $this->urls->addValues($diff);
        }

        public function getOne()
        {
                if($this->urls->getLength() < 1) {
                        return '';
                }
                $get = $this->urls->get();
                $this->got->addValue($get);
                return $get;
        }

        public function setContent(string $url, array $content)
        {
                //if(strpos($url,'story') !== false) {
                        $table = 'crawler.'.'udn_'.date('ymd');
                        $this->db->insert(array_merge(['url'=>$url], $content),$table)->execute();
                //}
        }


        private function getSet(string $key, Redis $redis)
        {
                $class = new class($redis) extends Set{
                        public function __construct(Redis $redis)
                        {
                                parent::__construct($redis);
                        }

                        public function setKey(string $key) {
                                $this->_key = $key;
                        }

                        public function getKey() : string
                        {
                                return $this->_key;
                        }
                };
                $class->setKey($key);
                return $class;
        }

}