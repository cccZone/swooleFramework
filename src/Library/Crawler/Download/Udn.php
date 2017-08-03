<?php


namespace Library\Crawler\Download;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class Udn implements Downloader
{
        use DownloadTrait;
        protected $content = '';
        protected $a = [];

        public function download(\Closure $callback = null)
        {
                try {
                        $request = new Request('GET', $this->url, [
                                "User-Agent" => 'Chrome/49.0.2587.3',
                                'Accept' => 'text/html,application/xhtml+xml,application/xml',
                                'Accept-Encoding' => 'gzip',
                                'referer' => 'https://udn.com/mobile/index',
                                'upgrade-insecure-requests' => '1'
                        ]);
                        $http = new Client(['timeout'  => 2]);
                        $res = $http->send($request)->getBody();
                        $this->content = $res->getContents();
                        unset($request, $http);
                        call_user_func_array($callback, [$this->url, $this->content]);
                }catch (\Exception $exception) {
                        unset($request, $http);
                        call_user_func_array($callback, [$this->url, '']);
                }

               /*$http->sendAsync($request)->then(function ($response) use ($callback){
                        $this->content = $response->getBody();
                        call_user_func_array($callback, [$this->url, $this->content]);
                });*/
        }

        public function getContent(): string
        {
                return $this->content;
        }


}