<?php
namespace app\index\controller;
use think\Db;

class Test
{
    public function index()
    {
        echo 'Test_index';
//         $res = Db::table('tcp_port')->select();
//         var_dump($res);

//         for ($i = 1; $i <= 1000; $i++) {
//             $data = [
//                 'port' => $i,
//                 'create_time' => date('Y-m-d H:i:s')
//             ];
            
//             Db::table('tcp_port')->insert($data);
//         }
        
        
    }
}
