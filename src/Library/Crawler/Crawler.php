<?php


namespace Library\Crawler;

use Kernel\Core;
use Library\Crawler\Download\Downloader;
use Library\Crawler\Download\Udn;
use Library\Crawler\Parse\Udn as Parse;
use Library\Crawler\Url\Udn as Url;

class Crawler
{
        protected $urlManager;
        protected $parserManager;
        protected $downloadManager;
        protected $init = [
                'urls'  =>      [],
                'path'  =>      ''
        ];
        public function __construct(Downloader $downloader, Parse $parser, Url $url)
        {
                $this->downloadManager = $downloader;
                $this->parserManager = $parser;
                $this->urlManager = $url;
        }

        public function initUrls(array $urls, string $path = '')
        {
               $this->init['urls'] = $urls;
               $this->init['path'] = $path;
               $this->reset();
        }

        public function reset()
        {
                $this->urlManager->addUrls($this->init['urls']);
                $this->parserManager->setPath($this->init['path']);
        }

        public function clear()
        {
                $this->urlManager->clear();
        }

        public function getUrl()
        {
                return $this->urlManager->getOne();
        }

        public function run(string $url)
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
                if (!is_array($data)) {
                        $data = json_decode($data, true);
                }

                $urls = is_array($data['url']) ? $data['url'] : [$data['url']];
                $path = $data['path'] ?? '';
                $core = Core::getInstant();
                $task = new Crawler(new Udn(), new Parse(), new Url($core->get('config'), $core->get('db'), $core->get('redis')));
                $task->initUrls($urls, $path);
                return $task;

        }

}