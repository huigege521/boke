<?php

namespace App\Controllers\Admin;

use App\Models\TagModel;
use CodeIgniter\Controller;

/**
 * 标签控制器
 * 负责标签的管理，包括列表、创建、编辑、删除等操作
 */
class TagController extends Controller
{
    /**
     * 构造函数
     * 检查登录状态和权限
     */
    public function __construct()
    {
        // 检查登录状态
        if (!session()->get('logged_in')) {
            header('Location: /admin/login');
            exit();
        }

        // 检查权限
        if (!session()->get('role') || session()->get('role') == 'user') {
            header('Location: /?error=权限不足');
            exit();
        }
    }

    /**
     * 标签列表
     * 获取所有标签并显示在列表页面，支持分页
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function index()
    {
        $tagModel = new TagModel();

        // 分页设置
        $perPage = 10; // 每页显示10条
        $page = $this->request->getVar('page') ?? 1; // 当前页码
        $offset = ($page - 1) * $perPage; // 偏移量

        // 获取标签列表
        $tags = $tagModel->getAllTags($perPage, $offset);

        // 获取标签总数
        $totalTags = $tagModel->getAllTagsCount();
        $totalPages = ceil($totalTags / $perPage); // 总页数

        // 准备视图数据
        $data = [
            'title' => '标签管理 - 后台',
            'tags' => $tags,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalTags,
                'per_page' => $perPage,
                'base_url' => '/admin/tags'
            ]
        ];

        // 渲染标签列表视图
        return view('admin/tags/index', $data);
    }

    /**
     * 创建标签
     * 显示创建标签的表单页面
     *
     * @return string 视图字符串
     */
    public function create()
    {
        // 准备视图数据
        $data = [
            'title' => '创建标签 - 后台',
        ];

        // 渲染创建标签表单视图
        return view('admin/tags/create', $data);
    }

    /**
     * 存储标签
     * 接收表单数据并保存到数据库
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function store()
    {
        $tagModel = new TagModel();

        // 验证表单数据
        $rules = [
            'name' => 'required|min_length[2]|max_length[50]|is_unique[tags.name]', // 名称必填，长度2-50，且唯一
            'slug' => 'required|min_length[2]|max_length[50]|is_unique[tags.slug]', // 别名必填，长度2-50，且唯一
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $errorMessage = '验证失败：' . implode('；', $errors);
            session()->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        // 准备标签数据
        $tagData = [
            'name' => $this->request->getVar('name'), // 标签名称
            'slug' => $this->request->getVar('slug'), // 标签别名
            'description' => $this->request->getVar('description'), // 标签描述
        ];

        // 创建标签
        if (!$tagModel->insert($tagData)) {
            session()->setFlashdata('error', '创建标签失败');
            return redirect()->back()->withInput();
        }

        // 重定向到标签列表页面并显示成功消息
        session()->setFlashdata('success', '创建标签成功');
        return redirect()->to('/admin/tags');
    }

    /**
     * 编辑标签
     * 根据ID获取标签数据并显示在编辑表单中
     *
     * @param int $id 标签ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function edit($id)
    {
        $tagModel = new TagModel();

        // 获取标签数据
        $tag = $tagModel->find($id);
        if (!$tag) {
            session()->setFlashdata('error', '标签不存在');
            return redirect()->to('/admin/tags');
        }

        // 准备视图数据
        $data = [
            'title' => '编辑标签 - 后台',
            'tag' => $tag,
        ];

        // 渲染编辑标签表单视图
        return view('admin/tags/edit', $data);
    }

    /**
     * 更新标签
     * 根据ID更新标签数据
     *
     * @param int $id 标签ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function update($id)
    {
        $tagModel = new TagModel();

        // 检查标签是否存在
        $tag = $tagModel->find($id);
        if (!$tag) {
            session()->setFlashdata('error', '标签不存在');
            return redirect()->to('/admin/tags');
        }

        // 验证表单数据
        $rules = [
            'name' => 'required|min_length[2]|max_length[50]', // 名称必填，长度2-50
            'slug' => 'required|min_length[2]|max_length[50]', // 别名必填，长度2-50
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $errorMessage = '验证失败：' . implode('；', $errors);
            session()->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        // 检查名称是否已存在
        if ($this->request->getVar('name') != $tag['name']) {
            if ($tagModel->where('name', $this->request->getVar('name'))->countAllResults() > 0) {
                session()->setFlashdata('error', '标签名称已存在');
                return redirect()->back()->withInput();
            }
        }

        // 检查slug是否已存在
        if ($this->request->getVar('slug') != $tag['slug']) {
            if ($tagModel->where('slug', $this->request->getVar('slug'))->countAllResults() > 0) {
                session()->setFlashdata('error', '标签别名已存在');
                return redirect()->back()->withInput();
            }
        }

        // 准备标签数据
        $tagData = [
            'name' => $this->request->getVar('name'), // 标签名称
            'slug' => $this->request->getVar('slug'), // 标签别名
            'description' => $this->request->getVar('description'), // 标签描述
        ];

        // 更新标签
        if (!$tagModel->update($id, $tagData)) {
            session()->setFlashdata('error', '更新标签失败');
            return redirect()->back()->withInput();
        }

        // 重定向到标签列表页面并显示成功消息
        session()->setFlashdata('success', '更新标签成功');
        return redirect()->to('/admin/tags');
    }

    /**
     * 删除标签
     * 根据ID删除标签
     *
     * @param int $id 标签ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function delete($id)
    {
        $tagModel = new TagModel();

        // 检查标签是否存在
        $tag = $tagModel->find($id);
        if (!$tag) {
            session()->setFlashdata('error', '标签不存在');
            return redirect()->to('/admin/tags');
        }

        // 检查是否有文章使用此标签
        if ($tag['posts_count'] > 0) {
            session()->setFlashdata('error', '该标签下有文章，无法删除');
            return redirect()->back();
        }

        // 删除标签
        if (!$tagModel->delete($id)) {
            session()->setFlashdata('error', '删除标签失败');
            return redirect()->back();
        }

        // 删除标签关联
        $db = \Config\Database::connect();
        $db->table('post_tags')->where('tag_id', $id)->delete();

        // 重定向到标签列表页面并显示成功消息
        session()->setFlashdata('success', '删除标签成功');
        return redirect()->to('/admin/tags');
    }

    /**
     * 显示标签（资源路由必需）
     * 重定向到编辑页面，因为后台管理中通常不需要单独的显示页面
     *
     * @param int $id 标签ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function show($id)
    {
        // 重定向到编辑页面，因为后台管理中通常不需要单独的显示页面
        return redirect()->to('/admin/tags/' . $id . '/edit');
    }
}