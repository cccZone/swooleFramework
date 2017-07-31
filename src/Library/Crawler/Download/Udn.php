<?php


namespace Library\Crawler\Download;


class Udn implements Downloader
{
        use DownloadTrait;
        protected $content;

        public function download(\Closure $callback = null) : Downloader
        {
                \Swoole\Async::dnsLookup($this->getUrlInfo('host'), function ($domainName, $ip) use($callback) {
                        if($domainName == '' or $ip =='') {
                                return;
                        }

                        $port = $this->getUrlInfo('port');

                        if('https' == $this->getUrlInfo('scheme')) {
                                $cli = new \swoole_http_client($ip,  !empty($port) ? $port : 443, true);
                        }else{
                                $cli = new \swoole_http_client($ip,  !empty($port) ? $port : 80);
                        }

                        $cli->setHeaders([
                                'Host' => $domainName,
                                "User-Agent" => 'Chrome/49.0.2587.3',
                                'Accept' => 'text/html,application/xhtml+xml,application/xml',
                                'Accept-Encoding' => 'gzip',
                        ]);
                        $path = $this->getUrlInfo('path');
                        $cli->get(!empty($path)?:'/', function ($cli) use ($callback) {
                                call_user_func_array($callback, [$this->url, $this->content]);
                                $data = $this->getMeta($cli->body);
                                if(!empty($data)) {
                                        $this->content = $cli->body;
                                }
                                $cli->close();
                        });
                });
                return $this;
        }

        public function getContent() : string
        {
                return '';
        }
}