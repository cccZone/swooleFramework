<?php


namespace Library\Crawler\Url;

use Kernel\Core;
use Kernel\Core\Cache\Redis;
use Kernel\Core\Cache\Type\Set;
use function PHPSTORM_META\type;

class Udn
{
        /* @var $urls Set */
        protected $urls;
        /* @var $got Set */
        protected $got;
        protected $db;
        protected $cache;
        protected $host;
        protected $dbName;
        protected $dbPrefix;
        public function __construct(string $host, string $prefix)
        {
                $core = Core::getInstant();
                $this->db = $core->get('db');
                $this->cache = $core->get('redis');
                $this->setHost($host);
                $this->setDbPrefix($prefix);
        }

        public function setHost(string $host)
        {
                $this->host = $host;
                $domain = explode('.', $host);

                if(count($domain)>2) {
                        unset($domain[0]);
                }
                $this->dbName = implode('_', $domain);
                if(!empty($this->dbPrefix)) {
                        $this->dbName = $this->dbPrefix.'_'.$this->dbName;
                }

                $this->_fixDbName();
                $this->urls = $this->getSet($host.':'.date('ymd').':urls', $this->cache);
                $this->got = $this->getSet($host.':'.date('ymd').':got', $this->cache);
                $this->clear();
        }

        private function _fixDbName()
        {
                $this->dbName = str_replace('.','_', $this->dbName);
        }

        public function setDbPrefix(string $prefix)
        {
                $this->dbPrefix = $prefix;
        }

        public function addUrls(array $urls)
        {
              $got = $this->got->getAll();
              if(!is_array($got)) {
                      $diff = array_diff($urls, $got);
                      if (!empty($diff)) {
                              $this->urls->addValues($diff);
                      }
              }else{
                      $this->urls->addValues($urls);
              }
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
               $table = 'crawler.'.$this->dbName.'_'.date('ymd');
               $this->db->insert(array_merge(['url'=>$url], $content),$table)->execute();
        }

        public function clear()
        {
                $this->got->del();
                $this->urls->del();
        }


        private function getSet(string $key, Redis $redis)
        {
                $class = new Set($redis);
                $class->setKey($key);
                return $class;
        }

}