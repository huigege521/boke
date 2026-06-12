<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * 用户管理 API 控制器
 * 
 * 提供用户的 RESTful API 接口，支持完整的 CRUD 操作
 * 部分接口需要管理员权限
 * 
 * @package App\Controllers\Api
 */
class UserController extends BaseApiController
{
    /**
     * @var UserModel 用户模型实例
     */
    protected $userModel;

    /**
     * 控制器初始化方法
     * 
     * @param RequestInterface $request 请求接口
     * @param ResponseInterface $response 响应接口
     * @param LoggerInterface $logger 日志接口
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel = new UserModel();
    }

    /**
     * 获取用户列表
     * 
     * 需要管理员权限，支持分页，按创建时间降序排列
     * GET /api/users
     * 
     * @return JSON 用户列表数据
     */
    public function index()
    {
        try {

            // 管理员权限验证
            if (session()->get('role') != 'admin') {
                return $this->error('权限不足', 403);
            }

            // 获取分页参数
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;

            // 构建查询
            $builder = $this->userModel->builder();
            $total = $builder->countAllResults(false);

            // 分页查询
            $offset = ($page - 1) * $perPage;
            $users = $builder->orderBy('created_at', 'desc')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // 处理返回数据
            foreach ($users as &$user) {
                // 角色文本
                $user['role_text'] = $user['role'] == 'admin' ? '管理员' : '普通用户';
                // 编辑链接
                $user['edit_url'] = base_url('admin/users/' . $user['id'] . '/edit');
                // 移除敏感信息
                unset($user['password']);
            }

            return $this->success([
                'list' => $users,
                'pagination' => [
                    'page' => (int) $page,
                    'per_page' => (int) $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ], '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\UserController.index] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 获取单个用户详情
     * 
     * 需要登录，可查看自己或管理员可查看所有用户
     * GET /api/users/{id}
     * 
     * @param int|null $id 用户ID
     * @return JSON 用户详情数据
     */
    public function show($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询用户
            $user = $this->userModel->find($id);

            // 用户不存在
            if (!$user) {
                return $this->error('用户不存在', 404);
            }

            // 移除敏感信息
            unset($user['password']);

            return $this->success($user, '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\UserController.show] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 创建新用户
     * 
     * 需要管理员权限
     * POST /api/users
     * 
     * @return JSON 创建结果
     */
    public function create()
    {
        try {

            // 管理员权限验证
            if (session()->get('role') != 'admin') {
                return $this->error('权限不足', 403);
            }

            // 获取请求数据（支持 FormData 和 JSON）
            $data = $this->request->getPost();
            if (empty($data)) {
                $data = $this->request->getJSON(true) ?? [];
            }

            // 验证规则
            $rules = [
                'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
                'email' => 'required|valid_email|is_unique[users.email]',
                'name' => 'required|max_length[100]',
                'password' => 'required|min_length[6]',
                'password_confirm' => 'required|matches[password]',
            ];

            // 数据验证
            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                $errorMessage = '验证失败：' . implode('；', $errors);
                return $this->error($errorMessage, 400);
            }

            // 准备保存数据（密码加密）
            $saveData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'name' => $data['name'] ?? '',
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => $data['role'] ?? 'user',
                'status' => $data['status'] ?? 'active',
            ];

            // 插入数据库
            $id = $this->userModel->insert($saveData);

            if (!$id) {
                return $this->error('创建失败', 500);
            }

            return $this->success(['id' => $id], '创建成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\UserController.create] ' . $e->getMessage());
            return $this->error('创建失败，请稍后重试', 500);
        }
    }

    /**
     * 更新用户信息
     * 
     * 用户可更新自己，管理员可更新所有用户
     * PUT /api/users/{id}
     * 
     * @param int|null $id 用户ID
     * @return JSON 更新结果
     */
    public function update($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询用户
            $user = $this->userModel->find($id);

            // 用户不存在
            if (!$user) {
                return $this->error('用户不存在', 404);
            }

            // 权限判断：管理员可修改所有用户，用户只能修改自己
            $isAdmin = session()->get('role') == 'admin';
            $isSelf = session()->get('user_id') == $id;

            if (!$isAdmin && !$isSelf) {
                return $this->error('权限不足', 403);
            }

            // 获取请求数据（支持 FormData 和 JSON）
            $data = $this->request->getPost();
            if (empty($data)) {
                $data = $this->request->getJSON(true) ?? [];
            }

            // 验证规则（允许为空，更新时不强制必填）
            $rules = [
                'username' => 'permit_empty|min_length[3]|max_length[50]|is_unique[users.username,id,' . $id . ']',
                'email' => 'permit_empty|valid_email|is_unique[users.email,id,' . $id . ']',
                'name' => 'permit_empty|max_length[100]',
            ];

            // 只有管理员可以修改角色
            if ($isAdmin) {
                $rules['role'] = 'permit_empty|in_list[admin,editor,user]';
            }

            // 如果有密码，则验证密码
            if ($this->request->getVar('password')) {
                $rules['password'] = 'required|min_length[6]';
                $rules['password_confirm'] = 'required|matches[password]';
            }

            // 数据验证
            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                $errorMessage = '验证失败：' . implode('；', $errors);
                return $this->error($errorMessage, 400);
            }
            // 准备更新数据（按需更新）
            $updateData = [];

            if (!empty($data['username'])) {
                $updateData['username'] = $data['username'];
            }

            if (!empty($data['email'])) {
                $updateData['email'] = $data['email'];
            }

            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }

            if (!empty($data['password'])) {
                $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            // 只有管理员可修改角色和状态
            if ($isAdmin && isset($data['role'])) {
                $updateData['role'] = $data['role'];
            }

            if ($isAdmin && isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }

            // 更新数据库
            if (!empty($updateData) && !$this->userModel->update($id, $updateData)) {
                return $this->error('更新失败', 500);
            }

            return $this->success(null, '更新成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\UserController.update] ' . $e->getMessage());
            return $this->error('更新失败，请稍后重试', 500);
        }
    }

    /**
     * 删除用户
     * 
     * 需要管理员权限，且不能删除自己
     * DELETE /api/users/{id}
     * 
     * @param int|null $id 用户ID
     * @return JSON 删除结果
     */
    public function delete($id = null)
    {
        try {

            // 管理员权限验证
            if (session()->get('role') != 'admin') {
                return $this->error('权限不足', 403);
            }

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询用户
            $user = $this->userModel->find($id);

            // 用户不存在
            if (!$user) {
                return $this->error('用户不存在', 404);
            }

            // 不能删除自己
            if ($user['id'] == session()->get('user_id')) {
                return $this->error('不能删除自己', 400);
            }

            // 删除用户
            if (!$this->userModel->delete($id)) {
                return $this->error('删除失败', 500);
            }

            return $this->success(null, '删除成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\UserController.delete] ' . $e->getMessage());
            return $this->error('删除失败，请稍后重试', 500);
        }
    }
}
