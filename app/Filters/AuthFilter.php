<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 检查用户是否登录
        if (!session()->get('logged_in')) {
            $uri = $request->getUri();
            $path = $uri->getPath();
            
            // 判断是否是 API 请求
            if (strpos($path, '/api/') !== false) {
                // API 请求返回 JSON 错误响应
                return service('response')->setJSON([
                    'success' => false,
                    'message' => '未登录',
                    'data' => null
                ])->setStatusCode(401);
            }
            
            // 保存当前URL，登录后重定向回来
            session()->set('redirect_url', current_url());
            
            // 重定向到登录页面
            return redirect()->to('/home/login')->with('error', '请先登录');
        }

        // 如果指定了角色参数，检查用户是否有足够的权限
        if ($arguments) {
            $userRole = session()->get('role');
            $requiredRole = $arguments[0];

            // 权限层级：admin > editor > user
            $roles = ['user' => 1, 'editor' => 2, 'admin' => 3];
            $userRoleLevel = $roles[$userRole] ?? 0;
            $requiredRoleLevel = $roles[$requiredRole] ?? 0;

            if ($userRoleLevel < $requiredRoleLevel) {
                $uri = $request->getUri();
                $path = $uri->getPath();
                
                // 判断是否是 API 请求
                if (strpos($path, '/api/') !== false) {
                    // API 请求返回 JSON 错误响应
                    return service('response')->setJSON([
                        'success' => false,
                        'message' => '权限不足',
                        'data' => null
                    ])->setStatusCode(403);
                }
                
                return redirect()->to('/')->with('error', '权限不足');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 过滤器的后置处理
    }
}
