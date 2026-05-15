<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * 用户 API 控制器
 * 处理用户的 CRUD 操作
 */
class UserController extends BaseApiController
{
    /**
     * 模型实例
     * @var UserModel
     */
    protected $userModel;

    /**
     * 构造函数
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel = new UserModel();
    }

    /**
     * 获取用户列表
     * 支持分页、筛选、搜索
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 10;
        $role = $this->request->getGet('role');
        $status = $this->request->getGet('status');
        $keyword = $this->request->getGet('keyword');

        $builder = $this->userModel->builder();

        if ($role) {
            $builder->where('role', $role);
        }

        if ($status) {
            $builder->where('status', $status);
        }

        if ($keyword) {
            $builder->groupStart()
                ->like('username', $keyword)
                ->orLike('email', $keyword)
                ->orLike('name', $keyword)
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);

        $offset = ($page - 1) * $perPage;
        $users = $builder->select('id, username, email, name, avatar, role, status, created_at, last_login')
            ->orderBy('created_at', 'desc')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $data = [
            'list' => $users,
            'pagination' => [
                'page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];

        return $this->success($data, '获取用户列表成功');
    }

    /**
     * 获取用户详情
     *
     * @param int $id 用户ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function show($id)
    {
        $user = $this->userModel->select('id, username, email, name, avatar, bio, role, status, created_at, updated_at, last_login')
            ->find($id);

        if (!$user) {
            return $this->notFound('用户不存在');
        }

        return $this->success($user, '获取用户详情成功');
    }

    /**
     * 创建用户
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function create()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'name' => 'required|min_length[2]|max_length[50]',
            'role' => 'permit_empty|in_list[admin,editor,user]',
            'status' => 'permit_empty|in_list[active,inactive]'
        ];

        if (!$this->validate($rules)) {
            return $this->validationError($this->validator->getErrors());
        }

        $data = [
            'username' => $this->request->getVar('username'),
            'email' => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'name' => $this->request->getVar('name'),
            'avatar' => $this->request->getVar('avatar'),
            'bio' => $this->request->getVar('bio'),
            'role' => $this->request->getVar('role') ?? 'user',
            'status' => $this->request->getVar('status') ?? 'active'
        ];

        $userId = $this->userModel->insert($data);
        if (!$userId) {
            return $this->error('创建用户失败');
        }

        $user = $this->userModel->select('id, username, email, name, avatar, role, status, created_at')
            ->find($userId);

        return $this->success($user, '创建用户成功', 201);
    }

    /**
     * 更新用户
     *
     * @param int $id 用户ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function update($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->notFound('用户不存在');
        }

        $rules = [
            'username' => "permit_empty|min_length[3]|max_length[50]|is_unique[users.username,id,{$id}]",
            'email' => "permit_empty|valid_email|is_unique[users.email,id,{$id}]",
            'name' => 'permit_empty|min_length[2]|max_length[50]',
            'role' => 'permit_empty|in_list[admin,editor,user]',
            'status' => 'permit_empty|in_list[active,inactive]'
        ];

        if (!$this->validate($rules)) {
            return $this->validationError($this->validator->getErrors());
        }

        $data = [];
        if ($this->request->getVar('username')) {
            $data['username'] = $this->request->getVar('username');
        }
        if ($this->request->getVar('email')) {
            $data['email'] = $this->request->getVar('email');
        }
        if ($this->request->getVar('password')) {
            $data['password'] = password_hash($this->request->getVar('password'), PASSWORD_DEFAULT);
        }
        if ($this->request->getVar('name')) {
            $data['name'] = $this->request->getVar('name');
        }
        if ($this->request->getVar('avatar') !== null) {
            $data['avatar'] = $this->request->getVar('avatar');
        }
        if ($this->request->getVar('bio') !== null) {
            $data['bio'] = $this->request->getVar('bio');
        }
        if ($this->request->getVar('role')) {
            $data['role'] = $this->request->getVar('role');
        }
        if ($this->request->getVar('status')) {
            $data['status'] = $this->request->getVar('status');
        }

        if (empty($data)) {
            return $this->error('没有需要更新的数据');
        }

        if (!$this->userModel->update($id, $data)) {
            return $this->error('更新用户失败');
        }

        $updatedUser = $this->userModel->select('id, username, email, name, avatar, role, status, updated_at')
            ->find($id);

        return $this->success($updatedUser, '更新用户成功');
    }

    /**
     * 删除用户
     *
     * @param int $id 用户ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->notFound('用户不存在');
        }

        if (!$this->userModel->delete($id)) {
            return $this->error('删除用户失败');
        }

        return $this->success(null, '删除用户成功');
    }
}
