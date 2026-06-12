<?php

namespace App\Controllers\Api;

use App\Models\CommentModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * 评论管理 API 控制器
 * 
 * 提供评论的 RESTful API 接口，支持完整的 CRUD 操作和审核操作
 * 所有接口均需要用户登录验证
 * 
 * @package App\Controllers\Api
 */
class CommentController extends BaseApiController
{
    /**
     * @var CommentModel 评论模型实例
     */
    protected $commentModel;

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
        $this->commentModel = new CommentModel();
    }

    /**
     * 获取评论列表
     * 
     * 支持状态筛选和分页，按创建时间降序排列
     * GET /api/comments?status={status}
     * 
     * @return JSON 评论列表数据
     */
    public function index()
    {
        try {

            // 获取查询参数
            $status = $this->request->getGet('status') ?? '';
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;

            // 构建查询 - 关联用户表获取作者信息
            $builder = $this->commentModel->builder()
                ->select('comments.*, users.username as user_name, users.email as user_email')
                ->join('users', 'users.id = comments.user_id', 'left');

            // 状态筛选
            if (!empty($status) && in_array($status, ['approved', 'pending', 'spam'])) {
                $builder->where('status', $status);
            }

            // 统计总数
            $total = $builder->countAllResults(false);

            // 分页查询
            $offset = ($page - 1) * $perPage;
            $comments = $builder->orderBy('created_at', 'desc')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // 处理返回数据
            foreach ($comments as &$comment) {
                // 状态文本
                $comment['status_text'] = $comment['status'] == 'approved' ? '已审核' :
                    ($comment['status'] == 'pending' ? '待审核' : '垃圾');
                // 编辑链接
                $comment['edit_url'] = base_url('admin/comments/' . $comment['id'] . '/edit');

                // 确定作者名称：优先使用用户表的用户名，其次使用评论表的作者名称
                $comment['author'] = !empty($comment['user_name']) ? $comment['user_name'] :
                    (!empty($comment['author_name']) ? $comment['author_name'] : '匿名');

                // 确定作者邮箱：优先使用用户表的邮箱，其次使用评论表的邮箱
                $comment['email'] = !empty($comment['user_email']) ? $comment['user_email'] :
                    (!empty($comment['author_email']) ? $comment['author_email'] : '');
            }

            return $this->success([
                'list' => $comments,
                'pagination' => [
                    'page' => (int) $page,
                    'per_page' => (int) $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ], '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\CommentController.index] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 获取单个评论详情
     * 
     * GET /api/comments/{id}
     * 
     * @param int|null $id 评论ID
     * @return JSON 评论详情数据
     */
    public function show($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询评论
            $comment = $this->commentModel->find($id);

            // 评论不存在
            if (!$comment) {
                return $this->error('评论不存在', 404);
            }

            return $this->success($comment, '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\CommentController.show] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 创建新评论
     * 
     * POST /api/comments
     * 
     * @return JSON 创建结果
     */
    public function create()
    {
        try {

            // 获取请求数据（支持 FormData 和 JSON）
            $data = $this->request->getPost();
            if (empty($data)) {
                $data = $this->request->getJSON(true) ?? [];
            }

            // 验证规则
            $rules = [
                'post_id' => 'required|is_natural_no_zero',
                'author' => 'required|min_length[2]|max_length[100]',
                'email' => 'required|valid_email',
                'content' => 'required|min_length[5]',
            ];

            // 数据验证
            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                $errorMessage = '验证失败：' . implode('；', $errors);
                return $this->error($errorMessage, 400);
            }

            // 准备保存数据
            $saveData = [
                'post_id' => $data['post_id'],
                'author' => $data['author'],
                'email' => $data['email'],
                'content' => $data['content'],
                'status' => $data['status'] ?? 'pending',
            ];

            // 插入数据库
            $id = $this->commentModel->insert($saveData);

            if (!$id) {
                return $this->error('创建失败', 500);
            }

            return $this->success(['id' => $id], '创建成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\CommentController.create] ' . $e->getMessage());
            return $this->error('创建失败，请稍后重试', 500);
        }
    }

    /**
     * 更新评论
     * 
     * PUT /api/comments/{id}
     * 
     * @param int|null $id 评论ID
     * @return JSON 更新结果
     */
    public function update($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询评论
            $comment = $this->commentModel->find($id);

            // 评论不存在
            if (!$comment) {
                return $this->error('评论不存在', 404);
            }

            // 获取请求数据（支持 FormData 和 JSON）
            $data = $this->request->getPost();
            if (empty($data)) {
                $data = $this->request->getJSON(true) ?? [];
            }

            // 验证规则 - 只验证必填字段
            $rules = [
                'content' => 'required|min_length[5]',
            ];

            // 数据验证
            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                $errorMessage = '验证失败：' . implode('；', $errors);
                return $this->error($errorMessage, 400);
            }

            // 准备更新数据 - 只更新提供的字段
            $updateData = [
                'content' => $data['content'],
            ];

            // 如果提供了状态，则更新状态
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }

            // 更新数据库
            if (!$this->commentModel->update($id, $updateData)) {
                return $this->error('更新失败', 500);
            }

            return $this->success(null, '更新成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\CommentController.update] ' . $e->getMessage());
            return $this->error('更新失败，请稍后重试', 500);
        }
    }

    /**
     * 删除评论
     * 
     * DELETE /api/comments/{id}
     * 
     * @param int|null $id 评论ID
     * @return JSON 删除结果
     */
    public function delete($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询评论
            $comment = $this->commentModel->find($id);

            // 评论不存在
            if (!$comment) {
                return $this->error('评论不存在', 404);
            }

            // 删除评论
            if (!$this->commentModel->delete($id)) {
                return $this->error('删除失败', 500);
            }

            return $this->success(null, '删除成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\CommentController.delete] ' . $e->getMessage());
            return $this->error('删除失败，请稍后重试', 500);
        }
    }

    /**
     * 审核评论
     * 
     * 将评论状态设置为已审核
     * POST /api/comments/approve/{id}
     * 
     * @param int|null $id 评论ID
     * @return JSON 审核结果
     */
    public function approve($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询评论
            $comment = $this->commentModel->find($id);

            // 评论不存在
            if (!$comment) {
                return $this->error('评论不存在', 404);
            }

            // 更新状态为已审核
            if (!$this->commentModel->update($id, ['status' => 'approved'])) {
                return $this->error('审核失败', 500);
            }

            return $this->success(null, '审核成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\CommentController.approve] ' . $e->getMessage());
            return $this->error('审核失败，请稍后重试', 500);
        }
    }
}
