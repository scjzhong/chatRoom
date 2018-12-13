<?php
namespace app\index\controller;
use app\BaseController;
use think\Db;
use think\Session;

class Login extends BaseController
{
    public function index()
    {
//         if(request()->isPost()){
            $username = $this->_filter(request()->input('username'));
            $pwd = $this->_filter(request()->input('passwd'));
            $username = 'scjzhong@sina.com';
            $pwd = 'qwer1234';
            $user = Db::table('user')->where(['username' => $username])->find();
            if($this->createPassword($pwd, $user['salt']) != $user['password']){
                return $this->outputError('用户名或密码错误');
            }
            Session::set('user', ['user_id' => $user['id'], 'username' => $user['username'], 'nickname' => $user['nickname'], 'avatar' => $user['avatar']]);
            return $this->outputSuccess('登录成功');
            
//         }else{
//             return $this->fetch();
//         }
    }
    
    public function createPassword($pwd, $salt)
    {
        return md5(md5( $pwd ) . $salt);
    }
}
