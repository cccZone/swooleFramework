<?php


namespace Library\Crawler\Url;

class Udn
{
        protected $urls = [];
        protected $contents = [];

        public function addUrls(array $urls)
        {
                $gets = array_keys($this->contents);
                $this->urls = array_merge($this->urls, array_diff($urls, $gets));
                $this->urls = array_unique($this->urls);
        }

        public function getOne()
        {
                if(count($this->urls)<=0) {
                        yield '';
                }
                $get = array_shift($this->urls);
                $this->contents[$get] = '';
                yield $get;
        }

        public function setContent($url, $content)
        {
                $this->contents[$url] = $content;
                file_put_contents('content.txt',$url."\r\n".json_encode($content)."\r\n\r\n",FILE_APPEND);
        }
}