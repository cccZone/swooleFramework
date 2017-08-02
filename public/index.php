<?php

include '../vendor/autoload.php';
include '../src/Kernel/Core.php';
$app = new \Kernel\Core([ realpath('../src')], [realpath('../conf')]);
//var_dump($app->get('config'));
/*$app->serverStart($app->get('tcp'), function () use($app){
        var_dump($app);
});*/

$app->doCrawler(function (\Library\Crawler\Crawler $crawler){
        $crawler->run();
});
