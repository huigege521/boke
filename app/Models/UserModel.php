<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'username',
        'email',
        'password',
        'name',
        'avatar',
        'bio',
        'role',
        'status',
        'last_login',
        'reset_token',
        'reset_token_expires'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // 根据邮箱获取用户
    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    // 根据用户名获取用户
    public function getUserByUsername($username)
    {
        return $this->where('username', $username)->first();
    }

    // 根据ID获取用户
    public function getUserById($id)
    {
        return $this->find($id);
    }

    // 获取所有用户
    public function getAllUsers($limit = 10, $offset = 0)
    {
        return $this->orderBy('created_at', 'desc')
            ->limit($limit, $offset)
            ->findAll();
    }

    // 检查用户是否存在
    public function userExists($email, $username = null)
    {
        $builder = $this->builder();
        $builder->where('email', $email);
        if ($username) {
            $builder->orWhere('username', $username);
        }
        return $builder->countAllResults() > 0;
    }

    // 检查用户权限
    public function hasPermission($userId, $requiredRole)
    {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }

        // 权限层级：admin > editor > user
        $roles = ['user' => 1, 'editor' => 2, 'admin' => 3];
        $userRoleLevel = $roles[$user['role']] ?? 0;
        $requiredRoleLevel = $roles[$requiredRole] ?? 0;

        return $userRoleLevel >= $requiredRoleLevel;
    }

    // 更新用户最后登录时间
    public function updateLastLogin($userId)
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    // 统计用户数量
    public function countUsers($role = null)
    {
        $builder = $this->builder();
        if ($role) {
            $builder->where('role', $role);
        }
        return $builder->countAllResults();
    }

    // 获取活跃用户
    public function getActiveUsers($limit = 10)
    {
        return $this->where('status', 'active')
            ->orderBy('last_login', 'desc')
            ->limit($limit)
            ->findAll();
    }

    // 根据重置令牌获取用户
    public function getUserByResetToken($token)
    {
        return $this->where('reset_token', $token)
            ->where('status', 'active')
            ->first();
    }
}
