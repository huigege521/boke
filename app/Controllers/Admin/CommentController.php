<?php

namespace App\Controllers\Admin;

use App\Models\CommentModel;
use CodeIgniter\Controller;

/**
 * 评论控制器
 * 负责评论的管理，包括列表、审核、编辑、删除等操作
 */
class CommentController extends Controller
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
     * 评论列表（AJAX无感加载版）
     * 页面结构通过AJAX异步加载数据
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function index()
    {
        $data = [
            'title' => '评论管理 - 后台',
        ];

        // 渲染AJAX版评论列表视图
        return view('admin/comments/index_ajax', $data);
    }

    /**
     * 待审核评论（AJAX无感加载版）
     * 页面结构通过AJAX异步加载数据
     *
     * @return string 视图字符串
     */
    public function pending()
    {
        $data = [
            'title' => '待审核评论 - 后台',
            'status' => 'pending',
        ];

        // 渲染AJAX版评论列表视图
        return view('admin/comments/index_ajax', $data);
    }

    /**
     * 已通过评论（AJAX无感加载版）
     * 页面结构通过AJAX异步加载数据
     *
     * @return string 视图字符串
     */
    public function approved()
    {
        $data = [
            'title' => '已通过评论 - 后台',
            'status' => 'approved',
        ];

        // 渲染AJAX版评论列表视图
        return view('admin/comments/index_ajax', $data);
    }

    /**
     * 垃圾评论（AJAX无感加载版）
     * 页面结构通过AJAX异步加载数据
     *
     * @return string 视图字符串
     */
    public function spam()
    {
        $data = [
            'title' => '垃圾评论 - 后台',
            'status' => 'spam',
        ];

        // 渲染AJAX版评论列表视图
        return view('admin/comments/index_ajax', $data);
    }

    /**
     * 编辑评论（AJAX无感加载版）
     * 根据ID获取评论数据并显示在编辑表单中，数据通过AJAX异步加载和提交
     *
     * @param int $id 评论ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function edit($id)
    {
        $data = [
            'title' => '编辑评论 - 后台',
            'commentId' => $id,
        ];

        return view('admin/comments/edit_ajax', $data);
    }

    /**
     * 编辑评论（传统表单版）
     *
     * @param int $id 评论ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function editForm($id)
    {
        $commentModel = new CommentModel();

        $comment = $commentModel->getCommentById($id);
        if (!$comment) {
            session()->setFlashdata('error', '评论不存在');
            return redirect()->to('/admin/comments');
        }

        $data = [
            'title' => '编辑评论 - 后台',
            'comment' => $comment,
        ];

        return view('admin/comments/edit', $data);
    }

    /**
     * 更新评论
     * 根据ID更新评论数据
     *
     * @param int $id 评论ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function update($id)
    {
        $commentModel = new CommentModel();

        // 检查评论是否存在
        $comment = $commentModel->find($id);
        if (!$comment) {
            session()->setFlashdata('error', '评论不存在');
            return redirect()->to('/admin/comments');
        }

        // 验证表单数据
        $rules = [
            'content' => 'required|min_length[1]', // 评论内容必填且长度至少为1
            'status' => 'required|in_list[approved,pending,spam]', // 评论状态必填且只能是approved、pending或spam
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $errorMessage = '验证失败：' . implode('；', $errors);
            session()->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        // 准备评论数据
        $commentData = [
            'content' => $this->request->getVar('content'), // 评论内容
            'status' => $this->request->getVar('status'), // 评论状态
        ];

        // 更新评论
        if (!$commentModel->update($id, $commentData)) {
            session()->setFlashdata('error', '更新评论失败');
            return redirect()->back()->withInput();
        }

        // 重定向到评论列表页面并显示成功消息
        session()->setFlashdata('success', '更新评论成功');
        return redirect()->to('/admin/comments');
    }

    /**
     * 删除评论
     * 根据ID删除评论
     *
     * @param int $id 评论ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function delete($id)
    {
        $commentModel = new CommentModel();

        // 检查评论是否存在
        $comment = $commentModel->find($id);
        if (!$comment) {
            session()->setFlashdata('error', '评论不存在');
            return redirect()->to('/admin/comments');
        }

        // 删除评论
        if (!$commentModel->deleteComment($id)) {
            session()->setFlashdata('error', '删除评论失败');
            return redirect()->back();
        }

        // 重定向到评论列表页面并显示成功消息
        session()->setFlashdata('success', '删除评论成功');
        return redirect()->to('/admin/comments');
    }

    /**
     * 批量操作
     * 对选中的评论进行批量操作，如批量审核、标记为垃圾或删除
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function batchAction()
    {
        $commentModel = new CommentModel();
        $action = $this->request->getVar('action'); // 操作类型
        $commentIds = $this->request->getVar('comment_ids'); // 选中的评论ID数组

        // 检查是否选择了操作和评论
        if (!$action || !$commentIds) {
            session()->setFlashdata('error', '请选择评论和操作');
            return redirect()->back();
        }

        // 遍历选中的评论ID，执行相应的操作
        foreach ($commentIds as $commentId) {
            switch ($action) {
                case 'approve':
                    // 批量审核通过
                    $commentModel->updateCommentStatus($commentId, 'approved');
                    break;
                case 'spam':
                    // 批量标记为垃圾
                    $commentModel->updateCommentStatus($commentId, 'spam');
                    break;
                case 'delete':
                    // 批量删除
                    $commentModel->deleteComment($commentId);
                    break;
            }
        }

        // 重定向到原页面并显示成功消息
        session()->setFlashdata('success', '批量操作成功');
        return redirect()->back();
    }

    /**
     * 显示评论（资源路由必需）
     * 重定向到编辑页面，因为后台管理中通常不需要单独的显示页面
     *
     * @param int $id 评论ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function show($id)
    {
        // 重定向到编辑页面，因为后台管理中通常不需要单独的显示页面
        return redirect()->to('/admin/comments/' . $id . '/edit');
    }
}