<?php


namespace Library\Crawler;

use Library\Crawler\Download\Downloader;
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

        public function initUrls(array $urls)
        {
                $this->urlManager->addUrls($urls);
        }

        public function run()
        {
                while (true) {
                        $url = $this->urlManager->getOne();
                        $mem = memory_get_usage();
                        file_put_contents('memory_'.date('ymdhi'), "memery: {$mem}".PHP_EOL);
                        if ($url == '') {
                                return;
                        }
                        $this->downloadManager->setUrl($url);
                        $this->downloadManager->download(function ($url, $content){
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
        }

}