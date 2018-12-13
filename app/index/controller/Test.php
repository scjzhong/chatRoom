<?php
namespace app\index\controller;

use think\Db;
use think\Session;
use app\index\Base;

class Test extends Base
{
    public function index()
    {
        $user = Session::get('user');
        $roomId = $this->_filter(request()->param('room_id'));

        if(empty($roomId)){
            $this->redirect('Index/Test/noroom');
        }
        
        $this->assign('uid', $user['user_id']);
        $this->assign('room_id', $roomId);
        return $this->fetch();
    }
    
    public function noroom()
    {
        echo 'noroom';
    }
}