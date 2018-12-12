<?php
namespace app\server\controller;

class Chatroom
{
    protected $port = 9501;
    
    private $serv;
    
    public function __construct()
    {
        echo '__construct';    
    }
    
    public function index()
    {
        echo 'Chatroom';
    }
}
