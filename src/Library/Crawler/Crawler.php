<?php


namespace Library\Crawler;

use Library\Crawler\Download\Downloader;
use Library\Crawler\Download\Udn;
use Library\Crawler\Parse\Udn as Parse;
use Library\Crawler\Url\Udn as Url;

class Crawler
{
        protected $urlManager;
        protected $parserManager;
        protected $downloadManager;

        public function __construct(Downloader $downloader, Parse $parser, Url $url)
        {
                $this->downloadManager = $downloader;
                $this->parserManager = $parser;
                $this->urlManager = $url;
        }

        public function initUrls(string $path = '')
        {
               $this->parserManager->setPath($path);
        }


        public function clear()
        {
                $this->urlManager->clear();
        }

        public function getUrl()
        {
                return $this->urlManager->getOne();
        }

        public function run(string $url = '')
        {
                if($url == '') {
                        while (true) {
                                try {
                                        $url = $this->getUrl();
                                        //echo "url:".$url."\r\n";
                                        if($url=='') {
                                                break;
                                        }
                                        $this->_run($url);
                                }catch (\Exception $exception) {
                                        file_put_contents('exception', date('Y-m-d H:i:s').":\r\n".$exception->getTraceAsString()."\r\n\r\n", FILE_APPEND);
                                }
                        }
                }else {
                        $this->_run($url);
                }
        }

        private function _run(string $url)
        {
                $this->downloadManager->setUrl($url);
                $this->downloadManager->download(function ($url, $content) {
                        if ($content !== '') {
                                $this->parserManager->doParse($url, $content, $this->downloadManager->getUrlInfo('host'));
                                $urls = array_filter($this->parserManager->getUrls(), function ($v) {
                                        return $v != '';
                                });
                                $this->urlManager->addUrls($urls);
                                $this->urlManager->setContent($url, $this->parserManager->getMeta());
                        }
                });
        }

        public static function getCrawler(array $data): Crawler
        {
                $urls = is_array($data['url']) ? $data['url'] : [$data['url']];
                $info =  parse_url($urls[0]);
                $path = $data['path'] ?? '';
                $task = new Crawler(new Udn(), new Parse(),  new Url($info['host'],$data['flag']??''));
                $task->clear();
                $task->initUrls($path);
                $task->run($urls[0]);
                return $task;
        }

}