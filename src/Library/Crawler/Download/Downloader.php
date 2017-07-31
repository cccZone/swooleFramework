<?php


namespace Library\Crawler\Download;


interface Downloader
{
        public function download(\Closure $callback = null) : Downloader;
        public function getContent() : string;
}