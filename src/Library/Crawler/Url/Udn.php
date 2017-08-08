<?php


namespace Library\Crawler\Url;


use Kernel\Core\Cache\Redis;
use Kernel\Core\Conf\Config;
use Kernel\Core\DB\DB;
use Kernel\Core\Cache\Redis\Set;

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
        public function __construct(Config $config, DB $db, Redis $redis)
        {
                $this->db = $db;
                $this->cache = $redis;
        }

        public function setHost(string $host)
        {
                $this->host = $host;
                $this->dbName = explode('.', $host)[1] ?? 'crawler';
                $this->urls = $this->getSet($host.':'.date('ymd').':urls', $this->cache);
                $this->got = $this->getSet($host.':'.date('ymd').':got', $this->cache);
        }

        public function addUrls(array $urls)
        {
                if($this->host == '') {
                        $url = parse_url($urls[0]);
                        $this->setHost($url['host']);
                }

                $got = $this->got->getAll();
                $diff = array_diff($urls, $got);
                if(!empty($diff)) {
                        $this->urls->addValues($diff);
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
                //if(strpos($url,'story') !== false) {
                        $table = 'crawler.'.$this->dbName.'_'.date('ymd');
                        $this->db->insert(array_merge(['url'=>$url], $content),$table)->execute();
                //}
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