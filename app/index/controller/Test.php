<?php
namespace app\index\controller;
use think\Db;
use app\BaseController;

class Test extends BaseController
{
    public function index()
    {
        return $this->fetch();
    }
}
