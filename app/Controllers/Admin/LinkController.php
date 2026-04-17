<?php

namespace App\Controllers\Admin;

use App\Models\LinkModel;
use CodeIgniter\Controller;

/**
 * 友情链接控制器
 * 负责友情链接的管理，包括列表、添加、编辑、删除等操作
 */
class LinkController extends Controller
{
    /**
     * 友情链接模型实例
     * @var LinkModel
     */
    protected $linkModel;

    /**
     * 构造函数
     * 检查登录状态和权限，初始化友情链接模型
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

        // 初始化友情链接模型
        $this->linkModel = new LinkModel();
    }

    /**
     * 友情链接列表页面
     * 获取所有友情链接并显示在列表页面
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function index()
    {
        // 准备视图数据
        $data = [
            'title' => '友情链接管理',
            'links' => $this->linkModel->findAll(), // 获取所有友情链接数据
        ];

        // 渲染友情链接列表视图
        return view('admin/links/index', $data);
    }

    /**
     * 添加友情链接页面
     * 显示添加友情链接的表单页面
     *
     * @return string 视图字符串
     */
    public function create()
    {
        // 准备视图数据
        $data = [
            'title' => '添加友情链接',
        ];

        // 渲染添加友情链接表单视图
        return view('admin/links/create', $data);
    }

    /**
     * 保存友情链接
     * 接收表单数据并保存到数据库
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function store()
    {
        // 验证表单数据
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'url' => 'required|valid_url',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 收集表单数据
        $data = [
            'name' => $this->request->getPost('name'),
            'url' => $this->request->getPost('url'),
            'description' => $this->request->getPost('description') ?? '',
            'logo' => $this->request->getPost('logo') ?? '',
            'sort_order' => $this->request->getPost('sort_order') ?? 0,
            'status' => $this->request->getPost('status') ?? 'active',
        ];

        // 插入数据到数据库
        if ($this->linkModel->insert($data)) {
            return redirect()->to('/admin/links')->with('success', '友情链接添加成功');
        } else {
            return redirect()->back()->withInput()->with('error', '友情链接添加失败');
        }
    }

    /**
     * 编辑友情链接页面
     * 根据ID获取友情链接数据并显示在编辑表单中
     *
     * @param int $id 友情链接ID
     * @return string 视图字符串
     */
    public function edit($id)
    {
        // 准备视图数据
        $data = [
            'title' => '编辑友情链接',
            'link' => $this->linkModel->find($id), // 根据ID获取友情链接数据
        ];

        // 渲染编辑友情链接表单视图
        return view('admin/links/edit', $data);
    }

    /**
     * 更新友情链接
     * 根据ID更新友情链接数据
     *
     * @param int $id 友情链接ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function update($id)
    {
        // 检查友情链接是否存在
        $link = $this->linkModel->find($id);
        if (!$link) {
            return redirect()->to('/admin/links')->with('error', '友情链接不存在');
        }

        // 验证表单数据
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'url' => 'required|valid_url',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 收集表单数据
        $data = [
            'name' => $this->request->getPost('name'),
            'url' => $this->request->getPost('url'),
            'description' => $this->request->getPost('description') ?? '',
            'logo' => $this->request->getPost('logo') ?? '',
            'sort_order' => $this->request->getPost('sort_order') ?? 0,
            'status' => $this->request->getPost('status') ?? 'active',
        ];

        // 更新数据库中的数据
        if ($this->linkModel->update($id, $data)) {
            return redirect()->to('/admin/links')->with('success', '友情链接更新成功');
        } else {
            return redirect()->back()->withInput()->with('error', '友情链接更新失败');
        }
    }

    /**
     * 删除友情链接
     * 根据ID删除友情链接
     *
     * @param int $id 友情链接ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function delete($id)
    {
        // 检查友情链接是否存在
        $link = $this->linkModel->find($id);
        if (!$link) {
            return redirect()->to('/admin/links')->with('error', '友情链接不存在');
        }

        // 从数据库中删除友情链接
        if ($this->linkModel->delete($id)) {
            return redirect()->to('/admin/links')->with('success', '友情链接删除成功');
        } else {
            return redirect()->back()->with('error', '友情链接删除失败');
        }
    }
}
