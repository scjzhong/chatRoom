<?php

/**
 * @author scjzhong
 * @date 2018年12月12日 上午10:04:33
 */

namespace app\service;

class RedisService
{
    private static $_instance;
    
    public $redis;
    
    protected $options = [
        'host'       => '47.100.161.112',
        'port'       => 6380,
        'password'   => 'redis_nihao123###',
        'select'     => 14,
        'timeout'    => 0,
        'expire'     => 0,
        'persistent' => false,
        'prefix'     => '',
    ];
    
    private function __construct()
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');
        }
        
        $this->connect();
    }
    
    private function connect()
    {
        
        $this->redis = new \Redis;
        if ($this->options['persistent']) {
            $this->redis->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], 'persistent_id_' . $this->options['select']);
        } else {
            $this->redis->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
        }
        
        if ('' != $this->options['password']) {
            $this->redis->auth($this->options['password']);
        }
        
        if (0 != $this->options['select']) {
            $this->redis->select($this->options['select']);
        }
    }
    
    public static function getInstance()
    {
        if(empty(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    
    public function __call($method, $args = [])
    {
        $reConnect = false;
        while (1){
            try{
                $result = call_user_func_array([$this->redis, $method], $args);
            }catch (\RedisException $e){
                //已重连过，仍然报错
                if ($reConnect){
                    throw $e;
                }
                
                if ($this->redis->isConnected()){
                    $this->redis->close();
                }
                $this->connect();
                $reConnect = true;
                continue;
            }
            return $result;
        }
        //不可能到这里
        return false;
    }
    
}

