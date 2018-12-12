#!/usr/bin/env php
<?php

// 定义应用目录
define('APP_PATH', __DIR__ . '/../app/');

// 绑定模块
define('BIND_MODULE', 'server/Chatroom');

// 定义 root 路径
define('ROOT_PATH', __DIR__ . '/../');

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';