<?php

namespace App\Controllers\Admin;

use App\Models\CategoryModel;
use CodeIgniter\Controller;

/**
 * 分类控制器
 * 负责分类的管理，包括列表、创建、编辑、删除等操作
 */
class CategoryController extends Controller
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
     * 分类列表
     * 获取所有分类并显示在列表页面，支持分页
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function index()
    {
        $categoryModel = new CategoryModel();

        // 分页设置
        $perPage = 10; // 每页显示10条
        $page = $this->request->getVar('page') ?? 1; // 当前页码
        $offset = ($page - 1) * $perPage; // 偏移量

        // 获取分类列表
        $categories = $categoryModel->getAllCategories($perPage, $offset);

        // 获取分类总数
        $totalCategories = $categoryModel->getAllCategoriesCount();
        $totalPages = ceil($totalCategories / $perPage); // 总页数

        // 准备视图数据
        $data = [
            'title' => '分类管理 - 后台',
            'categories' => $categories,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalCategories,
                'per_page' => $perPage,
                'base_url' => '/admin/categories'
            ]
        ];

        // 渲染分类列表视图
        return view('admin/categories/index', $data);
    }

    /**
     * 创建分类
     * 显示创建分类的表单页面
     *
     * @return string 视图字符串
     */
    public function create()
    {
        $categoryModel = new CategoryModel();

        // 准备视图数据
        $data = [
            'title' => '创建分类 - 后台',
            'parentCategories' => $categoryModel->getAllCategories(), // 所有父分类
        ];

        // 渲染创建分类表单视图
        return view('admin/categories/create', $data);
    }

    /**
     * 存储分类
     * 接收表单数据并保存到数据库
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function store()
    {
        $categoryModel = new CategoryModel();

        // 验证表单数据
        $rules = [
            'name' => 'required|min_length[2]|max_length[50]|is_unique[categories.name]', // 名称必填，长度2-50，且唯一
            'slug' => 'required|min_length[2]|max_length[50]|is_unique[categories.slug]', // 别名必填，长度2-50，且唯一
            'parent_id' => 'permit_empty|is_natural', // 父分类ID可选，且为自然数
            'order' => 'required|is_natural', // 排序必填，且为自然数
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $errorMessage = '验证失败：' . implode('；', $errors);
            session()->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        // 准备分类数据
        $categoryData = [
            'name' => $this->request->getVar('name'), // 分类名称
            'slug' => $this->request->getVar('slug'), // 分类别名
            'description' => $this->request->getVar('description'), // 分类描述
            'parent_id' => $this->request->getVar('parent_id') ?: null, // 父分类ID，为空则为顶级分类
            'order' => $this->request->getVar('order'), // 排序
            'icon' => $this->request->getVar('icon') ?: null, // 分类图标
        ];

        // 创建分类
        if (!$categoryModel->insert($categoryData)) {
            session()->setFlashdata('error', '创建分类失败');
            return redirect()->back()->withInput();
        }

        // 重定向到分类列表页面并显示成功消息
        session()->setFlashdata('success', '创建分类成功');
        return redirect()->to('/admin/categories');
    }

    /**
     * 编辑分类
     * 根据ID获取分类数据并显示在编辑表单中
     *
     * @param int $id 分类ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function edit($id)
    {
        $categoryModel = new CategoryModel();

        // 获取分类数据
        $category = $categoryModel->find($id);
        if (!$category) {
            session()->setFlashdata('error', '分类不存在');
            return redirect()->to('/admin/categories');
        }

        // 准备视图数据
        $data = [
            'title' => '编辑分类 - 后台',
            'category' => $category,
            'parentCategories' => $categoryModel->where('id !=', $id)->findAll(), // 所有父分类（排除当前分类）
        ];

        // 渲染编辑分类表单视图
        return view('admin/categories/edit', $data);
    }

    /**
     * 更新分类
     * 根据ID更新分类数据
     *
     * @param int $id 分类ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function update($id)
    {
        $categoryModel = new CategoryModel();

        // 检查分类是否存在
        $category = $categoryModel->find($id);
        if (!$category) {
            session()->setFlashdata('error', '分类不存在');
            return redirect()->to('/admin/categories');
        }

        // 验证表单数据
        $rules = [
            'name' => 'required|min_length[2]|max_length[50]', // 名称必填，长度2-50
            'slug' => 'required|min_length[2]|max_length[50]', // 别名必填，长度2-50
            'parent_id' => 'permit_empty|is_natural', // 父分类ID可选，且为自然数
            'order' => 'required|is_natural', // 排序必填，且为自然数
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $errorMessage = '验证失败：' . implode('；', $errors);
            session()->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        // 检查名称是否已存在
        if ($this->request->getVar('name') != $category['name']) {
            if ($categoryModel->where('name', $this->request->getVar('name'))->countAllResults() > 0) {
                session()->setFlashdata('error', '分类名称已存在');
                return redirect()->back()->withInput();
            }
        }

        // 检查slug是否已存在
        if ($this->request->getVar('slug') != $category['slug']) {
            if ($categoryModel->where('slug', $this->request->getVar('slug'))->countAllResults() > 0) {
                session()->setFlashdata('error', '分类别名已存在');
                return redirect()->back()->withInput();
            }
        }

        // 准备分类数据
        $categoryData = [
            'name' => $this->request->getVar('name'), // 分类名称
            'slug' => $this->request->getVar('slug'), // 分类别名
            'description' => $this->request->getVar('description'), // 分类描述
            'parent_id' => $this->request->getVar('parent_id') ?: null, // 父分类ID，为空则为顶级分类
            'order' => $this->request->getVar('order'), // 排序
            'icon' => $this->request->getVar('icon') ?: null, // 分类图标
        ];

        // 更新分类
        if (!$categoryModel->update($id, $categoryData)) {
            session()->setFlashdata('error', '更新分类失败');
            return redirect()->back()->withInput();
        }

        // 重定向到分类列表页面并显示成功消息
        session()->setFlashdata('success', '更新分类成功');
        return redirect()->to('/admin/categories');
    }

    /**
     * 删除分类
     * 根据ID删除分类
     *
     * @param int $id 分类ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function delete($id)
    {
        $categoryModel = new CategoryModel();

        // 检查分类是否存在
        $category = $categoryModel->find($id);
        if (!$category) {
            session()->setFlashdata('error', '分类不存在');
            return redirect()->to('/admin/categories');
        }

        // 检查是否有子分类
        if ($categoryModel->where('parent_id', $id)->countAllResults() > 0) {
            session()->setFlashdata('error', '该分类下有子分类，无法删除');
            return redirect()->back();
        }

        // 检查是否有文章
        if ($category['posts_count'] > 0) {
            session()->setFlashdata('error', '该分类下有文章，无法删除');
            return redirect()->back();
        }

        // 删除分类
        if (!$categoryModel->delete($id)) {
            session()->setFlashdata('error', '删除分类失败');
            return redirect()->back();
        }

        // 重定向到分类列表页面并显示成功消息
        session()->setFlashdata('success', '删除分类成功');
        return redirect()->to('/admin/categories');
    }

    /**
     * 显示分类（资源路由必需）
     * 重定向到编辑页面，因为后台管理中通常不需要单独的显示页面
     *
     * @param int $id 分类ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function show($id)
    {
        // 重定向到编辑页面，因为后台管理中通常不需要单独的显示页面
        return redirect()->to('/admin/categories/' . $id . '/edit');
    }
}