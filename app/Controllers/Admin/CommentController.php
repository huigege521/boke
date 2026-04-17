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
     * 评论列表
     * 获取所有评论并显示在列表页面，支持搜索和分页
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function index()
    {
        $commentModel = new CommentModel();

        // 获取搜索参数
        $search = $this->request->getVar('search') ?? '';

        // 分页设置
        $perPage = 10; // 每页显示10条
        $page = $this->request->getVar('page') ?? 1; // 当前页码
        $offset = ($page - 1) * $perPage; // 偏移量

        // 获取评论列表
        $comments = $commentModel->getAllComments($perPage, $offset, $search);

        // 获取评论总数
        $totalComments = $commentModel->getAllCommentsCount($search);
        $totalPages = ceil($totalComments / $perPage); // 总页数

        // 准备视图数据
        $data = [
            'title' => '评论管理 - 后台',
            'comments' => $comments,
            'search' => $search,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalComments,
                'per_page' => $perPage,
                'base_url' => '/admin/comments' . (!empty($search) ? '?search=' . urlencode($search) : '')
            ]
        ];

        // 渲染评论列表视图
        return view('admin/comments/index', $data);
    }

    /**
     * 待审核评论
     * 获取待审核状态的评论列表，支持搜索和分页
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function pending()
    {
        $commentModel = new CommentModel();

        // 获取搜索参数
        $search = $this->request->getVar('search') ?? '';

        // 分页设置
        $perPage = 10; // 每页显示10条
        $page = $this->request->getVar('page') ?? 1; // 当前页码
        $offset = ($page - 1) * $perPage; // 偏移量

        // 获取待审核评论列表
        $comments = $commentModel->getCommentsByStatus('pending', $perPage, $offset, $search);

        // 获取待审核评论总数
        $totalComments = $commentModel->getCommentsCountByStatus('pending', $search);
        $totalPages = ceil($totalComments / $perPage); // 总页数

        // 准备视图数据
        $data = [
            'title' => '待审核评论 - 后台',
            'comments' => $comments,
            'status' => 'pending',
            'search' => $search,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalComments,
                'per_page' => $perPage,
                'base_url' => '/admin/comments/pending' . (!empty($search) ? '?search=' . urlencode($search) : '')
            ]
        ];

        // 渲染评论列表视图
        return view('admin/comments/index', $data);
    }

    /**
     * 已通过评论
     * 获取已通过状态的评论列表，支持搜索和分页
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function approved()
    {
        $commentModel = new CommentModel();

        // 获取搜索参数
        $search = $this->request->getVar('search') ?? '';

        // 分页设置
        $perPage = 10; // 每页显示10条
        $page = $this->request->getVar('page') ?? 1; // 当前页码
        $offset = ($page - 1) * $perPage; // 偏移量

        // 获取已通过评论列表
        $comments = $commentModel->getCommentsByStatus('approved', $perPage, $offset, $search);

        // 获取已通过评论总数
        $totalComments = $commentModel->getCommentsCountByStatus('approved', $search);
        $totalPages = ceil($totalComments / $perPage); // 总页数

        // 准备视图数据
        $data = [
            'title' => '已通过评论 - 后台',
            'comments' => $comments,
            'status' => 'approved',
            'search' => $search,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalComments,
                'per_page' => $perPage,
                'base_url' => '/admin/comments/approved' . (!empty($search) ? '?search=' . urlencode($search) : '')
            ]
        ];

        // 渲染评论列表视图
        return view('admin/comments/index', $data);
    }

    /**
     * 垃圾评论
     * 获取垃圾状态的评论列表，支持搜索和分页
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function spam()
    {
        $commentModel = new CommentModel();

        // 获取搜索参数
        $search = $this->request->getVar('search') ?? '';

        // 分页设置
        $perPage = 10; // 每页显示10条
        $page = $this->request->getVar('page') ?? 1; // 当前页码
        $offset = ($page - 1) * $perPage; // 偏移量

        // 获取垃圾评论列表
        $comments = $commentModel->getCommentsByStatus('spam', $perPage, $offset, $search);

        // 获取垃圾评论总数
        $totalComments = $commentModel->getCommentsCountByStatus('spam', $search);
        $totalPages = ceil($totalComments / $perPage); // 总页数

        // 准备视图数据
        $data = [
            'title' => '垃圾评论 - 后台',
            'comments' => $comments,
            'status' => 'spam',
            'search' => $search,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalComments,
                'per_page' => $perPage,
                'base_url' => '/admin/comments/spam' . (!empty($search) ? '?search=' . urlencode($search) : '')
            ]
        ];

        // 渲染评论列表视图
        return view('admin/comments/index', $data);
    }

    /**
     * 编辑评论
     * 根据ID获取评论数据并显示在编辑表单中
     *
     * @param int $id 评论ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function edit($id)
    {
        $commentModel = new CommentModel();

        // 根据ID获取评论数据
        $comment = $commentModel->getCommentById($id);
        if (!$comment) {
            session()->setFlashdata('error', '评论不存在');
            return redirect()->to('/admin/comments');
        }

        // 准备视图数据
        $data = [
            'title' => '编辑评论 - 后台',
            'comment' => $comment,
        ];

        // 渲染编辑评论表单视图
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