<?php


namespace Library\Crawler\Parse;


class Udn
{
        protected $content;
        protected $url;
        protected $urls = [];
        public function doParse($url, $content)
        {
                $this->url = $url;
                $this->content = $content;
                $this->getMeta();
                $this->getUrls();
        }

        private function getMeta()
        {
                $data = $this->content;
                if(empty($data)) {
                        return;
                }
                $meta = [];
                preg_match('/<TITLE>([\w\W]*?)<\/TITLE>/si', $data, $matches);
                if (!empty($matches[1])) {
                        $meta['title'] = $matches[1];
                }
                preg_match('/<META\s+name="keywords"\s+content="([\w\W]*?)"/si', $data, $matches);
                if (empty($matches[1])) {
                        preg_match("/<META\s+name='keywords'\s+content='([\w\W]*?)'/si", $data, $matches);
                }
                if (empty($matches[1])) {
                        preg_match('/<META\s+content="([\w\W]*?)"\s+name="keywords"/si', $data, $matches);
                }
                if (empty($matches[1])) {
                        preg_match('/<META\s+http-equiv="keywords"\s+content="([\w\W]*?)"/si', $data, $matches);
                }
                if (!empty($matches[1])) {
                        $meta['keywords'] = $matches[1];
                }
                unset($matches);
                #Description
                preg_match('/<META\s+name="description"\s+content="([\w\W]*?)"/si', $data, $matches);
                if (empty($matches[1])) {
                        preg_match("/<META\s+name='description'\s+content='([\w\W]*?)'/si", $data, $matches);
                }
                if (empty($matches[1])) {
                        preg_match('/<META\s+content="([\w\W]*?)"\s+name="description"/si', $data, $matches);
                }
                if (empty($matches[1])) {
                        preg_match('/<META\s+http-equiv="description"\s+content="([\w\W]*?)"/si', $data, $matches);
                }
                if (!empty($matches[1])) {
                        $meta['description'] = $matches[1];
                }
                unset($matches);
                #image
                $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
                preg_match_all($pattern,$data,$matches);
                foreach ($matches as $key => $match) {
                        if(isset($match[0]) and !empty($match[0])){
                                if(strpos($match[0],'logo') === false) {
                                        $meta['image'] = $match[0];
                                }
                        }
                }
                return $meta;
        }

        private function getUrls()
        {
                $pattern = '#(http|ftp|https)://?([a-z0-9_-]+\.)+(com|net|cn|org){1}(\/[a-z0-9_-]+)*\.?(?!:jpg|jpeg|gif|png|bmp)(?:")#i';
                preg_match_all($pattern, $this->content, $matched);
                foreach ($matched[0] as $url) {
                        $this->urls[] = $url;
                }
        }

}