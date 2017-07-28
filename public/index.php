<?php

include '../vendor/autoload.php';
include '../src/Kernel/Core.php';
$appPath = realpath('../src');
$paths = [$appPath];
$app = new \Kernel\Core($paths);
$app->autoload();


/*
$dbManager = new \Illuminate\Database\Capsule\Manager();
$dbManager->addConnection($conf['db']);*/


