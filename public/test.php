<?php

$server = new swoole_http_server('0.0.0.0',9999);
$tickId = '';
$server->set([
        'worker_num'=>2,
   //     'task_worker_num'=>2
]);
$pids = [];

$server->on('Request', function($request, $response) use($server, &$pids){
        //$response->cookie("User", "Swoole");
        //$response->header("X-Server", "Swoole");

        //$server->task('dddd');
        if($request->server['request_uri'] == '/favicon.ico') {
                $response->end();
                return;
        }
        $response->end("<h1>Hello Swoole!</h1>");
        $pid = newProcess();
        $server->after(5000, function () use($pid){
                swoole_process::kill($pid);
                swoole_process::wait();
        });
});
$server->on('start',function (swoole_server $server){

});

function newProcess()
{
        $process = new swoole_process('test');
        $process->name('test1');
        return $process->start();
}

function test(swoole_process $worker)
{
        while (true) {
                echo 'timer'.PHP_EOL;
                sleep(2);
        }
}
$server->start();