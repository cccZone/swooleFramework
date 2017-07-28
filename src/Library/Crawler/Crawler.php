<?php


namespace Library\Crawler;


class Crawler
{
        private $url;
        private $toVisit = [];

        public function __construct($url)
        {
                $this->url = $url;
        }

        public function visitOneDegree()
        {
                $this->visit($this->url, function ($content) {
                        $this->loadPage($content);
                        $this->visitAll();
                });
        }


        private function loadPage($content)
        {
                //
              //  $pattern = '#((http|ftp|https)://(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)#i';
                $pattern = '#(http|ftp|https)://?([a-z0-9_-]+\.)+(com|net|cn|org){1}(\/[a-z0-9_-]+)*\.?(?!:jpg|jpeg|gif|png|bmp)(?:")#i';
                preg_match_all($pattern, $content, $matched);
                foreach ($matched[0] as $url) {
                        if (in_array($url, $this->toVisit)) {
                                continue;
                        }
                        $this->toVisit[] = rtrim($url,'"');
                        file_put_contents('urls',$url."\r\n",FILE_APPEND);
                }
        }

        private function visitAll()
        {
                foreach ($this->toVisit as $url) {
                        $this->visit($url);
                }
        }

        private function visit($url, $callback = null)
        {
                $urlInfo = parse_url($url);
                \Swoole\Async::dnsLookup($urlInfo['host'], function ($domainName, $ip) use($urlInfo,$url,$callback) {
                        if($domainName == '' or $ip =='') {
                                return;
                        }
                        if(!isset($urlInfo['port'])) {
                                if($urlInfo['scheme'] == 'https') {
                                        $urlInfo['port'] = 443;
                                }else{
                                        $urlInfo['port'] = 80;
                                }
                        }
                        if($urlInfo['scheme'] == 'https') {
                                $cli = new \swoole_http_client($ip,  $urlInfo['port'], true);
                        }else{
                                $cli = new \swoole_http_client($ip,  $urlInfo['port']);
                        }

                        $cli->setHeaders([
                                'Host' => $domainName,
                                "User-Agent" => 'Chrome/49.0.2587.3',
                                'Accept' => 'text/html,application/xhtml+xml,application/xml',
                                'Accept-Encoding' => 'gzip',
                        ]);
                        $cli->get($urlInfo['path']??'/', function ($cli) use ($callback,$url) {
                                if ($callback) {
                                        call_user_func($callback, $cli->body);
                                }
                                $data = $this->getMeta($cli->body);
                                if(!empty($data)) {
                                        file_put_contents('c',"url:{$url}"."\r\n",FILE_APPEND);
                                        foreach ($data as $key=>$item) {
                                                file_put_contents('c',$key.":".$item."\r\n",FILE_APPEND);
                                        }
                                        file_put_contents('c',"\r\n",FILE_APPEND);

                                }

                                $cli->close();
                        });

                });

        }

        private function getMeta(string $data)
        {
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
                var_dump($matches);
                if(isset($matches[1][0])){
                        $meta['image'] = $matches[1][0];
                }
                return $meta;
        }
}
