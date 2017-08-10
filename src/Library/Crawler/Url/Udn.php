<?php


namespace Library\Crawler\Url;

use Kernel\Core;
use Kernel\Core\Cache\Redis;
use Kernel\Core\Cache\Type\Set;

class Udn
{
        /* @var $urls Set */
        protected $urls;
        /* @var $got Set */
        protected $got;
        protected $db;
        protected $host;
        protected $dbName;
        protected $dbPrefix;
        public function __construct(string $host, string $dbName = '')
        {
                $core = Core::getInstant();
                /* @var \Kernel\Core\DB\Mongodb $db */
                $db = $core->get('db');
                $this->db = $db;
                $this->setHost($host);
                if(!empty($dbName)) {
                        $this->setDbName($dbName);
                }
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
                $this->urls = $this->getSet($host.':'.date('ymd').':urls');
                $this->got = $this->getSet($host.':'.date('ymd').':got');
                $this->clear();
        }

        private function _fixDbName()
        {
                $this->dbName = str_replace('.','_', $this->dbName);
        }

        public function setDbName(string $name)
        {
                $this->dbName = $name;
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
               $exists = $this->db->select('_id')->from($table)->where('url=?',[$url])->fetch(false);
               $content = array_merge(['url'=>$url], $content);
               if(!empty($exists)) {
                       $this->db->from($table)->update($content)->where('_id=?',[$exists['_id']])->execute();
               } else {
                       $this->db->insert(array_merge(['url' => $url], $content), $table)->execute();
               }
        }

        public function clear()
        {
                $this->got->del();
                $this->urls->del();
        }


        private function getSet(string $key)
        {
                $class = new Set(new Redis(Core::getInstant()->get('config'), false));
               // $class->select(5);
                $class->setKey($key);
                return $class;
        }

}