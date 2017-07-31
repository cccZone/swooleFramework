<?php


namespace Library\Crawler;


use Library\Crawler\Download\Udn as Download;
use Library\Crawler\Parse\Udn as Parse;
use Library\Crawler\Url\Udn as Url;

class Crawler
{
        protected $urlManager;
        protected $parserManager;
        protected $downloadManager;
        public function __construct()
        {
                $this->downloadManager = new Download();
                $this->parserManager = new Parse();
                $this->urlManager = new Url();
        }
}