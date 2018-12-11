<?php
namespace app\index\controller;
use app\index\Base;
use think\Session;

class Room extends Base
{
    public function index()
    {

    }
    
    public function play()
    {
        $user = Session::get('user');
        $this->assign('user_id', $user['user_id']);
        $this->assign('nickanme', $user['nickname']);
        $this->assign('avatar');
        return $this->fetch();
    }
}