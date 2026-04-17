<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use CodeIgniter\Controller;

/**
 * 用户控制器
 * 负责用户的管理，包括列表、创建、编辑、删除等操作
 */
class UserController extends Controller
{
    /**
     * 构造函数
     * 检查登录状态和权限（只有管理员可以管理用户）
     */
    public function __construct()
    {
        // 检查登录状态
        if (!session()->get('logged_in')) {
            header('Location: /home/login');
            exit();
        }

        // 检查权限（只有管理员可以管理用户）
        if (session()->get('role') != 'admin') {
            header('Location: /admin/dashboard?error=权限不足');
            exit();
        }
    }

    /**
     * 用户列表
     * 获取所有用户并显示在列表页面，支持分页
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function index()
    {
        $userModel = new UserModel();

        // 分页设置
        $perPage = 10; // 每页显示10条
        $page = $this->request->getVar('page') ?? 1; // 当前页码
        $offset = ($page - 1) * $perPage; // 偏移量

        // 获取用户列表
        $users = $userModel->getAllUsers($perPage, $offset);

        // 获取用户总数
        $totalUsers = $userModel->countAllResults();
        $totalPages = ceil($totalUsers / $perPage); // 总页数

        // 准备视图数据
        $data = [
            'title' => '用户管理 - 后台',
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalUsers,
                'per_page' => $perPage,
                'base_url' => '/admin/users'
            ]
        ];

        // 渲染用户列表视图
        return view('admin/users/index', $data);
    }

    /**
     * 创建用户
     * 显示创建用户的表单页面
     *
     * @return string 视图字符串
     */
    public function create()
    {
        // 准备视图数据
        $data = [
            'title' => '创建用户 - 后台',
        ];

        // 渲染创建用户表单视图
        return view('admin/users/create', $data);
    }

    /**
     * 存储用户
     * 接收表单数据并保存到数据库
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function store()
    {
        $userModel = new UserModel();

        // 验证表单数据
        $rules = [
            //'name' => 'required|min_length[2]|max_length[50]', // 姓名必填，长度2-50
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]', // 用户名必填，长度3-30，且唯一
            'email' => 'required|valid_email|is_unique[users.email]', // 邮箱必填，格式正确，且唯一
            'password' => 'required|min_length[6]', // 密码必填，长度至少6位
            'password_confirm' => 'matches[password]', // 确认密码必须与密码匹配
            'role' => 'required|in_list[admin,editor,user]', // 角色必填，只能是admin、editor或user
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $errorMessage = '验证失败：' . implode('；', $errors);
            session()->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        // 准备用户数据
        $userData = [
            //'name' => $this->request->getVar('name'), // 姓名
            'username' => $this->request->getVar('username'), // 用户名
            'email' => $this->request->getVar('email'), // 邮箱
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT), // 密码哈希
            'role' => $this->request->getVar('role'), // 角色
        ];

        // 创建用户
        if (!$userModel->insert($userData)) {
            session()->setFlashdata('error', '创建用户失败');
            return redirect()->back()->withInput();
        }

        // 重定向到用户列表页面并显示成功消息
        session()->setFlashdata('success', '创建用户成功');
        return redirect()->to('/admin/users');
    }

    /**
     * 编辑用户
     * 根据ID获取用户数据并显示在编辑表单中
     *
     * @param int $id 用户ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function edit($id)
    {
        $userModel = new UserModel();

        // 获取用户数据
        $user = $userModel->find($id);
        if (!$user) {
            session()->setFlashdata('error', '用户不存在');
            return redirect()->to('/admin/users');
        }

        // 准备视图数据
        $data = [
            'title' => '编辑用户 - 后台',
            'user' => $user,
        ];

        // 渲染编辑用户表单视图
        return view('admin/users/edit', $data);
    }

    /**
     * 更新用户
     * 根据ID更新用户数据
     *
     * @param int $id 用户ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function update($id)
    {
        $userModel = new UserModel();

        // 检查用户是否存在
        $user = $userModel->find($id);
        if (!$user) {
            session()->setFlashdata('error', '用户不存在');
            return redirect()->to('/admin/users');
        }

        // 验证表单数据
        $rules = [
            //'name' => 'required|min_length[2]|max_length[50]', // 姓名必填，长度2-50
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username,id,' . $id . ']', // 用户名必填，长度3-30，且唯一（排除当前用户）
            'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']', // 邮箱必填，格式正确，且唯一（排除当前用户）
            'role' => 'required|in_list[admin,editor,user]', // 角色必填，只能是admin、editor或user
        ];

        // 如果有密码，则验证密码
        if ($this->request->getVar('password')) {
            $rules['password'] = 'required|min_length[6]'; // 密码必填，长度至少6位
            $rules['password_confirm'] = 'matches[password]'; // 确认密码必须与密码匹配
        }

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $errorMessage = '验证失败：' . implode('；', $errors);
            session()->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        // 准备用户数据
        $userData = [
            //'name' => $this->request->getVar('name'), // 姓名
            'username' => $this->request->getVar('username'), // 用户名
            'email' => $this->request->getVar('email'), // 邮箱
            'role' => $this->request->getVar('role'), // 角色
        ];

        // 如果有密码，则更新密码
        if ($this->request->getVar('password')) {
            $userData['password'] = password_hash($this->request->getVar('password'), PASSWORD_DEFAULT); // 密码哈希
        }

        // 更新用户
        if (!$userModel->update($id, $userData)) {
            session()->setFlashdata('error', '更新用户失败');
            return redirect()->back()->withInput();
        }

        // 重定向到用户列表页面并显示成功消息
        session()->setFlashdata('success', '更新用户成功');
        return redirect()->to('/admin/users');
    }

    /**
     * 删除用户
     * 根据ID删除用户
     *
     * @param int $id 用户ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function delete($id)
    {
        $userModel = new UserModel();

        // 检查用户是否存在
        $user = $userModel->find($id);
        if (!$user) {
            session()->setFlashdata('error', '用户不存在');
            return redirect()->to('/admin/users');
        }

        // 不能删除当前登录用户
        if ($user['id'] == session()->get('user_id')) {
            session()->setFlashdata('error', '不能删除当前登录用户');
            return redirect()->back();
        }

        // 删除用户
        if (!$userModel->delete($id)) {
            session()->setFlashdata('error', '删除用户失败');
            return redirect()->back();
        }

        // 重定向到用户列表页面并显示成功消息
        session()->setFlashdata('success', '删除用户成功');
        return redirect()->to('/admin/users');
    }

    /**
     * 显示用户（资源路由必需）
     * 重定向到编辑页面，因为后台管理中通常不需要单独的显示页面
     *
     * @param int $id 用户ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function show($id)
    {
        // 重定向到编辑页面，因为后台管理中通常不需要单独的显示页面
        return redirect()->to('/admin/users/' . $id . '/edit');
    }
}