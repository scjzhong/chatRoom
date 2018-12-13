<?php
namespace app\server\controller;

use think\Db;
use Swoole\WebSocket\Server;
use app\service\RedisService;
use app\server\BaseServer;
use app\service\RedisKeyService;

class Chatroom extends BaseServer
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
        #初始化 数据库 redis 长连接
        Db::connect(config('database'));
        RedisService::getInstance();
    }
    
    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        $uid = empty($request->get['uid']) ? 0 : (int)$request->get['uid'];
        $roomId = empty($request->get['room_id']) ? 0 : (int)$request->get['room_id'];
        
        if(empty($uid) || empty($roomId)){
            $server->push($request->fd, $this->outputError('参数不正确'));
            $server->close($request->fd);
            return;
        }
        
        $user = Db::table('user')->where(['id' => $uid])->find();
        if(empty($user)){
            $server->push($request->fd, $this->outputError('用户不存在'));
            $server->close($request->fd);
            return;
        }
        
        #第一步 绑定用户
        $this->bindUser($uid, $request->fd);
        #第二步 加入房间
        $this->joinRoom($roomId, $uid, $request->fd);
        
        $server->push($request->fd, $this->outputSuccess("欢迎" . $user['nickname'] . "加入房间"));
    }
    
    
    public function onMessage(\swoole_websocket_server $server, \Swoole\WebSocket\Frame $frame)
    {
        $server->push($frame->fd, $this->outputSuccess($frame->data));
    }
    
    
    
    public function onClose(\swoole_websocket_server $server, $fd)
    {
        $this->untyingUser($fd);
        $roomId = $this->getRoomIdFdRoomIdMapByFd($fd);
        $this->exitRoom($roomId, $fd);
    }
    
    
    public function index()
    {
        //开启服务
        $this->serv->start();
    }
    
    
    /**
     * 绑定用户
     * @param int $uid
     * @param int $fd
     */
    protected function bindUser(int $uid, int $fd)
    {
        $this->setFdUidMap($fd, $uid);
        $this->setUidFdMap($uid, $fd);
    }
    
    /**
     * 解绑用户
     * @param int $fd
     */
    protected function untyingUser(int $fd)
    {
        $uid = $this->getUidByFd($fd);
        $this->delFdUidMapByFd($fd);
        $this->delUidFdMapByUid($uid);
    }
    
    /**
     * 加入房间
     * @param int $roomId
     * @param int $uid
     * @param int $fd
     */
    protected function joinRoom(int $roomId, int $uid, int $fd)
    {
        $this->roomPush($roomId, $fd, $uid);
        $this->setFdRoomIdMap($fd, $roomId);
    }
    
    /**
     * 解除 roomId fd 的绑定关系
     * @param int $roomId
     * @param int $fd
     */
    protected function exitRoom(int $roomId, int $fd)
    {
        $this->deleteFdRoomIdMapByFd($fd);
        $this->deleteRoomMapFd($roomId, $fd);
    }
    
    /**
     * 绑定 uid fd
     * @param int $uid
     * @param int $fd
     * @return int
     */
    protected function setUidFdMap(int $uid, int $fd)
    {
        return RedisService::getInstance()->redis->hSet(RedisKeyService::getUidFdMap(), $uid, $fd);
    }
    
    /**
     * 绑定fd uid
     * @param int $fd
     * @param int $uid
     * @return int
     */
    protected function setFdUidMap(int $fd, int $uid)
    {
        return RedisService::getInstance()->redis->hSet(RedisKeyService::getFdUidMap(), $fd, $uid);
    }

    /**
     * 获取 uid
     * @param int $fd
     * @return unknown
     */
    protected function getUidByFd(int $fd)
    {
        return (int) RedisService::getInstance()->redis->hGet(RedisKeyService::getFdUidMap(), $fd);
    }
    
    /**
     * 获取 fd
     * @param int $uid
     * @return unknown
     */
    protected function getFdByUid(int $uid)
    {
        return RedisService::getInstance()->redis->hGet(RedisKeyService::getUidFdMap(), $uid);
    }
    
    /**
     * 删除 uid->fd 对应关系
     * @param int $uid
     * @return unknown
     */
    protected function delUidFdMapByUid(int $uid)
    {
        return RedisService::getInstance()->redis->hDel(RedisKeyService::getUidFdMap(), $uid);
    }
    
    /**
     * 删除 uid->fd 对应关系
     * @param int $fd
     * @return unknown
     */
    protected function delFdUidMapByFd(int $fd)
    {
        return RedisService::getInstance()->redis->hDel(RedisKeyService::getFdUidMap(), $fd);
    }
    
    
    /**
     * 将fd 写到 room hash 中
     * @param int $roomId
     * @param int $fd
     * @param int $uid
     * @return unknown
     */
    protected function roomPush(int $roomId, int $fd, int $uid)
    {
        return RedisService::getInstance()->redis->hSet(RedisKeyService::getRoomMap($roomId), $fd, $uid);
    }
    
    /**
     * 删除房间中的fd
     * @param int $roomId
     * @param int $fd
     * @return unknown
     */
    protected function deleteRoomMapFd(int $roomId, int $fd)
    {
        return RedisService::getInstance()->redis->hDel(RedisKeyService::getRoomMap($roomId), $fd);
    }
    
    /**
     * 获取房间中所有的fd
     * @param int $roomId
     * @return unknown
     */
    protected function getRoomFdsByRoomId(int $roomId)
    {
        return RedisService::getInstance()->redis->hKeys(RedisKeyService::getRoomMap($roomId));
    }
    
    /**
     * 获取房间中所有的uid
     * @param int $roomId
     * @return unknown
     */
    protected function getRoomUidsByRoomId(int $roomId)
    {
        return RedisService::getInstance()->redis->hVals(RedisKeyService::getRoomMap($roomId));
    }
    
    /**
     * 设置 fd => $roomId
     * @param int $fd
     * @param int $roomId
     * @return unknown
     */
    protected function setFdRoomIdMap(int $fd, int $roomId)
    {
        return RedisService::getInstance()->redis->hSet(RedisKeyService::getFdRoomIdMap(), $fd, $roomId);
    }
    
    /**
     * 获取 fd 对应的 roomid
     * @param int $fd
     * @return number
     */
    protected function getRoomIdFdRoomIdMapByFd(int $fd)
    {
        return (int) RedisService::getInstance()->redis->hGet(RedisKeyService::getFdRoomIdMap(), $fd);
    }
    
    /**
     * 删除 fd => $roomId
     * @param int $fd
     * @return unknown
     */
    protected function deleteFdRoomIdMapByFd(int $fd)
    {
        return RedisService::getInstance()->redis->hDel(RedisKeyService::getFdRoomIdMap(), $fd);
    }
    
    
    
}
