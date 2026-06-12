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
     * 显示配置列表（AJAX无感加载版）
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        if (session()->get('role') !== 'admin') {
            return redirect()->to('/admin/dashboard');
        }

        $data = [
            'title' => '配置管理 - 后台管理',
            'pageTitle' => '配置管理',
            'activePage' => 'settings',
        ];

        return view('admin/settings/index_ajax', $data);
    }

    /**
     * 显示配置列表（传统版）
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function indexForm()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        if (session()->get('role') !== 'admin') {
            return redirect()->to('/admin/dashboard');
        }

        $settingModel = new SettingModel();
        $settings = $settingModel->findAll();

        $data = [
            'title' => '配置管理 - 后台管理',
            'pageTitle' => '配置管理',
            'activePage' => 'settings',
            'settings' => $settings,
        ];

        return view('admin/settings/index', $data);
    }

    /**
     * 编辑配置（AJAX无感加载版）
     *
     * @param int $id 配置ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function edit($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        if (session()->get('role') !== 'admin') {
            return redirect()->to('/admin/dashboard');
        }

        $data = [
            'title' => '编辑配置 - 后台管理',
            'pageTitle' => '编辑配置',
            'activePage' => 'settings',
            'settingId' => $id,
        ];

        return view('admin/settings/edit_ajax', $data);
    }

    /**
     * 编辑配置（传统版）
     *
     * @param int $id 配置ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function editForm($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        if (session()->get('role') !== 'admin') {
            return redirect()->to('/admin/dashboard');
        }

        $settingModel = new SettingModel();
        $setting = $settingModel->find($id);

        if (!$setting) {
            session()->setFlashdata('error', '配置不存在');
            return redirect()->to('/admin/settings');
        }

        $data = [
            'title' => '编辑配置 - 后台管理',
            'pageTitle' => '编辑配置',
            'activePage' => 'settings',
            'setting' => $setting,
        ];

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
