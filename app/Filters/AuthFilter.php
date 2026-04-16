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
            // 检查是否是API请求（如文件上传）
            $uri = $request->getUri();
            if (strpos($uri->getPath(), '/admin/posts/upload') !== false) {
                // 对于上传请求，返回JSON错误响应
                return service('response')->setJSON([
                    'error' => [
                        'message' => '用户未登录'
                    ]
                ])->setStatusCode(401);
            }
            
            // 保存当前URL，登录后重定向回来
            session()->set('redirect_url', current_url());
            
            // 重定向到登录页面
            return redirect()->to('/home/login')->with('error', '请先登录');
        }

        // 对于上传请求，跳过CSRF验证
        $uri = $request->getUri();
        if (strpos($uri->getPath(), '/admin/posts/upload') !== false) {
            return null;
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
                return redirect()->to('/')->with('error', '权限不足');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 过滤器的后置处理
    }
}
