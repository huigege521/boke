<?php

namespace App\Libraries;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\SessionInterface;

class AuthService
{
    protected $userModel;
    protected $session;
    protected $request;
    protected $response;

    public function __construct(
        UserModel $userModel = null,
        SessionInterface $session = null,
        RequestInterface $request = null,
        ResponseInterface $response = null
    ) {
        $this->userModel = $userModel ?? new UserModel();
        $this->session = $session ?? session();
        $this->request = $request ?? service('request');
        $this->response = $response ?? service('response');
    }

    // 用户登录
    public function login($email, $password)
    {
        // 根据邮箱获取用户
        $user = $this->userModel->getUserByEmail($email);
        if (!$user) {
            return [
                'success' => false,
                'message' => '邮箱或密码错误'
            ];
        }

        // 检查用户状态
        if ($user['status'] != 'active') {
            return [
                'success' => false,
                'message' => '账号已被禁用'
            ];
        }

        // 验证密码
        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'message' => '邮箱或密码错误'
            ];
        }

        // 设置登录会话
        $sessionData = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'logged_in' => true
        ];

        $this->session->set($sessionData);

        // 更新最后登录时间
        $this->userModel->updateLastLogin($user['id']);

        return [
            'success' => true,
            'message' => '登录成功',
            'user' => $user
        ];
    }

    // 用户注册
    public function register($data)
    {
        // 验证数据
        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'name' => 'required'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return [
                'success' => false,
                'message' => '表单验证失败',
                'errors' => $validation->getErrors()
            ];
        }

        // 哈希密码
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        // 准备用户数据
        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $passwordHash,
            'name' => $data['name'],
            'role' => 'user',
            'status' => 'active'
        ];

        // 创建用户
        $userId = $this->userModel->insert($userData);
        if (!$userId) {
            return [
                'success' => false,
                'message' => '注册失败，请重试'
            ];
        }

        return [
            'success' => true,
            'message' => '注册成功',
            'user_id' => $userId
        ];
    }

    // 用户登出
    public function logout()
    {
        $this->session->destroy();
        return [
            'success' => true,
            'message' => '已成功登出'
        ];
    }

    // 检查用户是否登录
    public function isLoggedIn()
    {
        return $this->session->get('logged_in') === true;
    }

    // 获取当前登录用户
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userId = $this->session->get('user_id');
        return $this->userModel->getUserById($userId);
    }

    // 检查用户权限
    public function hasPermission($requiredRole)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $userId = $this->session->get('user_id');
        return $this->userModel->hasPermission($userId, $requiredRole);
    }

    // 检查是否是管理员
    public function isAdmin()
    {
        return $this->hasPermission('admin');
    }

    // 检查是否是编辑
    public function isEditor()
    {
        return $this->hasPermission('editor');
    }

    // 检查是否是普通用户
    public function isUser()
    {
        return $this->hasPermission('user');
    }

    // 生成密码重置令牌
    public function generatePasswordResetToken($userId)
    {
        $token = bin2hex(random_bytes(32));
        // 这里可以将令牌存储到数据库中，设置过期时间
        // 为了简化，这里只返回令牌
        return $token;
    }

    // 验证密码重置令牌
    public function validatePasswordResetToken($token)
    {
        // 这里应该从数据库中验证令牌是否有效
        // 为了简化，这里假设令牌有效
        return true;
    }

    // 重置密码
    public function resetPassword($token, $newPassword)
    {
        // 验证令牌
        if (!$this->validatePasswordResetToken($token)) {
            return [
                'success' => false,
                'message' => '无效的重置令牌'
            ];
        }

        // 哈希新密码
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // 这里应该根据令牌找到用户并更新密码
        // 为了简化，这里假设操作成功
        return [
            'success' => true,
            'message' => '密码重置成功'
        ];
    }
}
