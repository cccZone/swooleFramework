<?php


namespace Library\Crawler\Parse;


class Udn
{
        protected $content;
        protected $url;
        protected $urls = [];
        protected $hosts = [];
        protected $meta = [];
        public function doParse($url, $content, $host)
        {
                $this->url = $url;
                $this->content = $content;
                $this->hosts = [$host];
                $this->meta = $this->_getMeta();
                $this->_getUrls();
        }

        private function _getMeta()
        {
                return Regular::getMeta($this->content);
        }

        public function setHosts(array $hosts = [])
        {
                $this->hosts = array_merge($this->hosts, $hosts);
        }

        public function getHosts():array
        {
                return $this->hosts;
        }

        public function getUrls():array
        {
                return $this->urls;
        }

        /**
         * @return array
         */
        public function getMeta(): array
        {
                return empty($this->meta)?[] : $this->meta;
        }



        private function _getUrls()
        {
                $urlInfo = parse_url($this->url);
                $this->urls = Regular::getUrls($this->content, $urlInfo['scheme']??'', $urlInfo['host']??'');
        }

}