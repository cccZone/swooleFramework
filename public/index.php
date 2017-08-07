<?php

include '../vendor/autoload.php';
include '../src/Kernel/Core.php';
$app = new \Kernel\Core([ realpath('../src')], [realpath('../conf')]);
//var_dump($app->get('config'));
$app->serverStart($app->get('http'), function () use($app){

});

/**
 * 爬虫示例
$app->getContainer()->alias(Kernel\Core\DB\DB::class,Kernel\Core\DB\Mongodb::class);
$app->doCrawler(function (\Library\Crawler\Crawler $crawler){
        $crawler->initUrls(['']);
        $crawler->run();
});
*/