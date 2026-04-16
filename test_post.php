<?php

require_once 'vendor/autoload.php';

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\IncomingRequest;

// 创建一个模拟请求
$request = new IncomingRequest(new Config\App(), new Config\Services());

// 打印POST参数
echo "POST参数：\n";
print_r($request->getPost());

// 打印所有参数
echo "\n所有参数：\n";
print_r($request->getVar());

// 打印原始输入
echo "\n原始输入：\n";
print_r($request->getRawInput());
?>