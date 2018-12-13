<?php

namespace app\server;

class BaseServer
{
    public function outputData($data = [], $msg = '')
    {
        $data = [
            'err_code' => 0,
            'data' => $data,
            'err_msg' => $msg
        ];
        return $this->outputJson($data);
    }
    
    public function outputSuccess($msg)
    {
        $data = [
            'err_code' => 0,
            'data' => [],
            'err_msg' => $msg
        ];
        return $this->outputJson($data);
    }
    
    public function outputError($msg, $errCode = -1)
    {
        $data = [
            'err_code' => $errCode,
            'data' => [],
            'err_msg' => $msg
        ];
        return $this->outputJson($data);
    }
    
    public function outputJson($data)
    {
        header('Content-Type:application/json; charset=utf-8');
        return json_encode($data);
    }
    
    /**
     * 过滤参数
     * @param array | string $params
     * @return array | string
     */
    protected function _filter($params)
    {
        if(is_array($params)){
            foreach ($params as $key => $param) {
                $params[$key] = trim(htmlspecialchars(strip_tags($param)));
            }
            return $params;
        }elseif(is_string($params)){
            return trim(htmlspecialchars(strip_tags($params)));
        }else{
            return $params;
        }
    }
    
}