<?php

$server = new swoole_http_server('0.0.0.0',9999);
$tickId = '';
$server->set([
        'worker_num'=>2,
        'dispatch_mode'=>3
]);
$pids = [];
function getRedis()
{
        $redis = new Redis();
        $redis->connect('54.222.155.203', 12346);
        $redis->select(8);
        return $redis;
}

$server->on('Request', function($request, $response) use($server){
        if($request->server['request_uri'] == '/favicon.ico') {
                $response->end();
                return;
        }
        $data = $request->rawContent();
        $data = json_decode($data, true);
        if(isset($data['action'])) {
                $action = $data['action'];
                if($action == 'start') {
                        start($data, $server);
                }else{
                        stop($data, $server);
                }
        }
        $response->end("<h1>Hello Swoole!</h1>");
      //  start($data,$server);
});
function start($data, swoole_server $server){
        $flag = $data['flag'];

        $redis = getRedis();
        $cache = $redis->hGetAll($flag);
        if(isset($cache['stop'])) {
                if($cache['stop'] == 0) {
                        $pid = newProcess($flag);
                        $redis->hSet($flag,'processId', $pid);
                }else{
                        $pid = '-1';
                }
        }else{
                $pid = newProcess($flag);
                $redis->hSet($flag,'processId', $pid);
                $redis->hSet($flag,'stop', 0);
        }
        $server->after($data['interval'] * 1000, function () use ($data,$server){
                $redis = getRedis();
                $cache = $redis->hGetAll($data['flag']);
                if (isset($cache['processId']) and $cache['stop'] == '0') {
                        echo "{$data['flag']} stop : {$cache['stop']}" . PHP_EOL;
                        echo "reload " . $data['flag'] . PHP_EOL;
                        start($data, $server);
                        swoole_process::kill($cache['processId']);
                        swoole_process::wait();
                }
        });
        return $pid;
}

function stop($data, swoole_server $server)
{
        $redis = getRedis();
        $cache = $redis->hGetAll($data['flag']);
        $redis->hSet($data['flag'],'stop',1);
        if (isset($cache['processId']) and $cache['stop'] == '0') {
                echo "{$data['flag']} stop : {$cache['stop']}" . PHP_EOL;
                echo "reload " . $data['flag'] . PHP_EOL;
                $processId = $cache['processId'];
                $server->tick(20,function ($id) use($processId){
                        echo "kill processId".$processId.PHP_EOL;
                        \swoole_process::kill($processId);
                        \swoole_process::wait(true);
                        \swoole_timer_clear($id);
                });
        }
}
function newProcess($data)
{
        $process = new swoole_process(function () use ($data){
                while (true) {
                        echo $data.' timer'.PHP_EOL;
                        sleep(2);
                }
        });
        $process->name('test1');
        return $process->start();
}

$server->start();







