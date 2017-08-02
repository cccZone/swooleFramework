<?php


namespace Library\Crawler\Url;


use Kernel\Core\Conf\Config;

class Udn
{
        protected $urls = [];
        protected $got = [];
        protected $db;
        public function __construct(Config $config)
        {

        }

        public function addUrls(array $urls)
        {
                $gets = $this->got;
                $this->urls = array_merge($this->urls, array_diff($urls, $gets));
                $this->urls = array_unique($this->urls);
        }

        public function getOne()
        {
                if(count($this->urls)<=0) {
                        return '';
                }
                $get = array_shift($this->urls);
                array_push($this->got, $get);
                return $get;
        }

        public function setContent($url, $content)
        {
                $mongodb = new Mon
        }
}