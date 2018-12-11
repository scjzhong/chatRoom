<?php

namespace app\index;
use think\Session;
use app\BaseController;

class Base extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        
        $session = Session::get('user');
        if(empty($session) || empty($session['user_id']) || empty($session['username'])){
            $this->redirect('/Index/login');
        }
    }
    
}