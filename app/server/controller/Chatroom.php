<?php
namespace app\server\controller;

use think\Db;
use Swoole\WebSocket\Server;
use app\service\RedisService;

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
            'log_file' => '/var/log/swoole/chatroom.log',
            //'daemonize' => 1,//开启守护进程模式后(daemonize => true)，标准输出将会被重定向到log_file
        ]);
        
        $this->serv->on('start', [$this, 'onStart']);
        $this->serv->on('workerStart', [$this, 'onWorkerStart']);
        $this->serv->on('open', [$this, 'onOpen']);
        $this->serv->on('message', [$this, 'onMessage']);
        $this->serv->on('close', [$this, 'onClose']);
    }
    
    /**
     * 创建指定 worker_num 的work进程
     */
    public function onStart($server)
    {
        echo 'start' . PHP_EOL;
    }
    
    

    /**
     * 
     * @param \swoole_websocket_server $server
     * @param int $worker_id
     */
    public function onWorkerStart(\swoole_websocket_server $server, int $worker_id)
    {
        echo $worker_id;
        echo PHP_EOL;
        #初始化 数据库 redis 长连接
        $res = Db::setQuery("select *");
        var_dump($res['id']);
        RedisService::getInstance();
    }
    
    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        
        echo 'onOpen：' . $request->fd;
        echo PHP_EOL;
    }
    
    
    public function onMessage(\swoole_websocket_server $server, \Swoole\WebSocket\Frame $frame)
    {
        echo 'onReceive：' . $frame->data;
        echo PHP_EOL;
    }
    
    
    
    public function onClose(\swoole_websocket_server $server, $fd)
    {
        echo 'onClose：' . $fd;
        echo PHP_EOL;
    }
    
    
    public function index()
    {
        $this->serv->start();
    }
}
