<?php


namespace Library\Crawler\Url;


class Udn
{
        protected $urls = [];
        protected $contents = [];

        private function loadPage($url)
        {

                foreach ($matched[0] as $url) {
                        if (in_array($url, $this->urls) and array_key_exists($url, $this->contents)) {
                                continue;
                        }
                        $this->urls[] = rtrim($url,'"');
                }
        }
}