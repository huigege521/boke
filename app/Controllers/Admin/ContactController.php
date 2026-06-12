<?php
namespace App\Controllers\Admin;

use App\Models\ContactModel;
use CodeIgniter\Controller;

/**
 * 联系消息控制器
 * 负责管理用户提交的联系消息
 */
class ContactController extends Controller
{
    /**
     * 显示联系消息列表（AJAX无感加载版）
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function index()
    {
        // 检查登录状态
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        // 准备视图数据（数据通过AJAX加载）
        $data = [
            'title' => '联系消息管理 - 后台管理',
        ];

        // 渲染AJAX版联系消息列表视图
        return view('admin/contacts/index_ajax', $data);
    }

    /**
     * 显示联系消息详情（AJAX无感加载版）
     *
     * @param int $id 联系消息ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function show($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        $data = [
            'title' => '联系消息详情 - 后台管理',
            'contactId' => $id,
        ];

        return view('admin/contacts/show_ajax', $data);
    }

    /**
     * 显示联系消息详情（传统版）
     *
     * @param int $id 联系消息ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function showForm($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        $contactModel = new ContactModel();
        $contact = $contactModel->find($id);

        if (!$contact) {
            session()->setFlashdata('error', '联系消息不存在');
            return redirect()->to('/admin/contacts');
        }

        $data = [
            'title' => '联系消息详情 - 后台管理',
            'contact' => $contact,
        ];

        return view('admin/contacts/show', $data);
    }

    /**
     * 删除联系消息
     *
     * @param int $id 联系消息ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function delete($id)
    {
        // 检查请求方法
        if (strtolower($this->request->getMethod()) !== 'delete') {
            return redirect()->to('/admin/contacts');
        }

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
        $contactModel = new ContactModel();

        // 检查联系消息是否存在
        if (!$contactModel->find($id)) {
            session()->setFlashdata('error', '联系消息不存在');
            return redirect()->to('/admin/contacts');
        }

        // 删除联系消息
        if ($contactModel->delete($id)) {
            session()->setFlashdata('success', '联系消息删除成功');
        } else {
            session()->setFlashdata('error', '联系消息删除失败');
        }

        // 重定向回联系消息列表
        return redirect()->to('/admin/contacts');
    }

    /**
     * 处理联系消息（标记为已处理）
     *
     * @param int $id 联系消息ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function process($id)
    {
        // 检查请求方法
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/admin/contacts');
        }

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
        $contactModel = new ContactModel();

        // 检查联系消息是否存在
        $contact = $contactModel->find($id);
        if (!$contact) {
            session()->setFlashdata('error', '联系消息不存在');
            return redirect()->to('/admin/contacts');
        }

        // 更新处理状态
        if ($contactModel->update($id, ['status' => 'processed'])) {
            session()->setFlashdata('success', '消息已标记为已处理');
        } else {
            session()->setFlashdata('error', '操作失败，请重试');
        }

        // 重定向回联系消息详情
        return redirect()->to('/admin/contacts/show/' . $id);
    }
}
