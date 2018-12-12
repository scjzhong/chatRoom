<?php
namespace app\server\controller;

use app\service\RedisService;
use think\Db;
use Swoole\WebSocket\Server;

class Chatroom
{
    private $host = '0.0.0.0'; 
    #端口
    private $port = 9501;
    #swoole 对象
    private $serv;
    
    public function __construct()
    {   
        $this->serv = new Server($this->host, $this->port);
        $this->serv->set([
            'worker_num' => 4,
            'max_request' => 1000,
            'log_file' => '/var/log/swoole/swoole.log',
            //'daemonize' => 1,//开启守护进程模式后(daemonize => true)，标准输出将会被重定向到log_file
        ]);
        
        $this->serv->on('satrt', [$this, 'onStart']);
        $this->serv->on('open', [$this, 'onOpen']);
        $this->serv->on('receive', [$this, 'onReceive']);
        $this->serv->on('close', [$this, 'onClose']);
    }
    
    /**
     * 创建指定 worker_num 的work进程
     */
    public function onStart($server)
    {
        var_dump($server);
        #初始化 数据库 redis 长连接
        Db::connect();
        RedisService::getInstance();
    }
    
    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        
        echo 'onOpen：' . $request->fd;
        echo PHP_EOL;
    }
    
    
    public function onReceive(\swoole_websocket_server $server, \Swoole\WebSocket\Frame $frame)
    {
        echo 'onReceive：' . $frame->data;
        echo PHP_EOL;
    }
    
    public function onClose(\swoole_websocket_server $server, $fd)
    {
        echo 'onClose：' . $fd;
        echo PHP_EOL;
    }
    
}
