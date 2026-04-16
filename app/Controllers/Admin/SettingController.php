<?php
namespace App\Controllers\Admin;

use App\Models\SettingModel;
use CodeIgniter\Controller;

/**
 * 配置管理控制器
 * 负责管理系统配置
 */
class SettingController extends Controller
{
    /**
     * 显示配置列表
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function index()
    {
        // 检查登录状态
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        // 检查管理员角色
        $userRole = session()->get('role');
        if ($userRole !== 'admin') {
            return redirect()->to('/admin/dashboard');
        }

        // 初始化模型
        $settingModel = new SettingModel();

        // 获取所有配置
        $settings = $settingModel->findAll();

        // 准备视图数据
        $data = [
            'title' => '配置管理 - 后台管理',
            'pageTitle' => '配置管理',
            'activePage' => 'settings',
            'settings' => $settings,
        ];

        // 渲染配置列表视图
        return view('admin/settings/index', $data);
    }

    /**
     * 编辑配置
     *
     * @param int $id 配置ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function edit($id)
    {
        // 检查登录状态
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        // 检查管理员角色
        $userRole = session()->get('role');
        if ($userRole !== 'admin') {
            return redirect()->to('/admin/dashboard');
        }

        // 初始化模型
        $settingModel = new SettingModel();

        // 获取配置详情
        $setting = $settingModel->find($id);

        if (!$setting) {
            session()->setFlashdata('error', '配置不存在');
            return redirect()->to('/admin/settings');
        }

        // 准备视图数据
        $data = [
            'title' => '编辑配置 - 后台管理',
            'pageTitle' => '编辑配置',
            'activePage' => 'settings',
            'setting' => $setting,
        ];

        // 渲染编辑配置视图
        return view('admin/settings/edit', $data);
    }

    /**
     * 更新配置
     *
     * @param int $id 配置ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function update($id)
    {
        // 检查登录状态
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        // 检查管理员角色
        $userRole = session()->get('role');
        if ($userRole !== 'admin') {
            return redirect()->to('/admin/dashboard');
        }

        // 检查请求方法
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/settings');
        }

        // 初始化模型
        $settingModel = new SettingModel();

        // 检查配置是否存在
        if (!$settingModel->find($id)) {
            session()->setFlashdata('error', '配置不存在');
            return redirect()->to('/admin/settings');
        }

        // 获取表单数据
        $data = [
            'setting_value' => $this->request->getPost('setting_value'),
        ];

        // 更新配置
        if ($settingModel->update($id, $data)) {
            session()->setFlashdata('success', '配置更新成功');
        } else {
            session()->setFlashdata('error', '配置更新失败');
        }

        // 重定向回配置列表
        return redirect()->to('/admin/settings');
    }
}
