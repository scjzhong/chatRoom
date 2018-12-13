<?php

namespace app\service;

class RedisKeyService
{
    /**
     * 获取房间key
     * @param int $roomId
     * @return string
     */
    public static function getRoomMap( int $roomId) :string
    {
        return 'room_' . $roomId;
    }
    
    public static function getFdRoomIdMap() :string
    {
        return 'fd_roomid';
    }
    
    /**
     * uid 和 fd 的对应关系
     * 获取fd
     * @return string
     */
    public static function getUidFdMap() :string
    {
        return "uid_fd";
    }
    
    /**
     * fd 和 uid 的对应关系
     * 获取uid
     * @return string
     */
    public static function getFdUidMap() :string
    {
        return "fd_uid";
    }
    
}

