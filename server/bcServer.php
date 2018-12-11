<?php

require_once './RedisService.php';
require_once './vendor/autoload.php';

use think\Db;

$server = new \swoole_websocket_server("0.0.0.0", 9501);
$server->set([
    'worker_num' => 4,
    'max_request' => 1000,
    'log_file' => '/var/log/swoole/swoole.log',
    //'daemonize' => 1,//开启守护进程模式后(daemonize => true)，标准输出将会被重定向到log_file
]);


$server->on('WorkerStart', function ($serv, $worker_id){//初始化db实例
    $init = Db::table('number')->find();
    echo $init['id'] . '-' . $worker_id . PHP_EOL;
    RedisService::getInstance();
});

$server->on('open', function (swoole_websocket_server $_server, swoole_http_request $request) {
    //echo $request->fd;
    //echo PHP_EOL;
    $_server->push($request->fd, json_encode(['type' => 'fd', 'fd' => $request->fd]));
});
    
$server->on('message', function (swoole_websocket_server $_server, $frame) {
    
    //     $data = $frame->data;
    //     echo $data;
    //     echo "\n";
    //     foreach($_server->connections as $fd){
    //         echo "$fd\n";
    //         $_server->push($fd , $data);//循环广播
    //     }
    
    $res = Db::table('number')->where(['id' => 1])->find();
    
    echo $res['award_numbers'];
    
    echo PHP_EOL;
    
    $flag = RedisService::getInstance()->redis->incr('test_workerid_' . $_server->worker_id, 1);
    var_dump($flag);
    echo PHP_EOL;
    //var_dump($frame->data);
    $data = json_decode($frame->data);
    //	var_dump($data);
    if(!empty($data->type) && $data->type == 'jump'){
        $_server->push($data->fd, json_encode(['type' => 'jump','is_jump' => 'jump']));
    }
    $data = null;
});
        
/**
 * 当客户端关闭与服务端的连接时会触发该事件
 * 客户端 关闭浏览器/当前窗口 均会触发该事件
 * 关闭后 $fd 会从 $_server->connections 中移除
 */
$server->on('close', function (swoole_websocket_server $_server, $fd) {
    //echo "client {$fd} closed\n";
});
    
$server->start();
            
