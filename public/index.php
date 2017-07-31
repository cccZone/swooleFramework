<?php

include '../vendor/autoload.php';
include '../src/Kernel/Core.php';
$app = new \Kernel\Core([ realpath('../src')], [realpath('../conf')]);



