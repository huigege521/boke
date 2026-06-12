<?php

namespace App\Controllers\Api;

use App\Models\ContactModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * 联系我们管理 API 控制器
 * 
 * 提供联系我们消息的 RESTful API 接口，支持列表查询、详情查看、删除和处理操作
 * 所有接口均需要用户登录验证
 * 
 * @package App\Controllers\Api
 */
class ContactController extends BaseApiController
{
    /**
     * @var ContactModel 联系我们模型实例
     */
    protected $contactModel;

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
        $this->contactModel = new ContactModel();
    }

    /**
     * 获取联系我们消息列表
     * 
     * 支持状态筛选和分页，按创建时间降序排列
     * GET /api/contacts?status={status}
     * 
     * @return JSON 消息列表数据
     */
    public function index()
    {
        try {

            // 获取查询参数
            $status = $this->request->getGet('status');
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;

            // 构建查询
            $builder = $this->contactModel->builder();

            // 状态筛选
            if ($status && in_array($status, ['pending', 'processed'])) {
                $builder->where('status', $status);
            }

            // 统计总数
            $total = $builder->countAllResults(false);

            // 分页查询
            $offset = ($page - 1) * $perPage;
            $contacts = $builder->orderBy('created_at', 'desc')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // 处理返回数据
            foreach ($contacts as &$contact) {
                // 状态文本
                $contact['status_text'] = $contact['status'] === 'processed' ? '已处理' : '未处理';
                // 状态样式类
                $contact['status_class'] = $contact['status'] === 'processed' ? 'success' : 'warning';
                // 查看链接
                $contact['show_url'] = base_url('admin/contacts/show/' . $contact['id']);
                // 删除链接
                $contact['delete_url'] = base_url('admin/contacts/delete/' . $contact['id']);
            }

            return $this->success([
                'list' => $contacts,
                'pagination' => [
                    'page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ], '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\ContactController.index] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 获取单个消息详情
     * 
     * GET /api/contacts/{id}
     * 
     * @param int|null $id 消息ID
     * @return JSON 消息详情数据
     */
    public function show($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询消息
            $contact = $this->contactModel->find($id);

            // 消息不存在
            if (!$contact) {
                return $this->error('消息不存在', 404);
            }

            return $this->success($contact, '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\ContactController.show] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 删除消息
     * 
     * DELETE /api/contacts/{id}
     * 
     * @param int|null $id 消息ID
     * @return JSON 删除结果
     */
    public function delete($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询消息
            $contact = $this->contactModel->find($id);

            // 消息不存在
            if (!$contact) {
                return $this->error('消息不存在', 404);
            }

            // 删除消息
            if (!$this->contactModel->delete($id)) {
                return $this->error('删除失败', 500);
            }

            return $this->success(null, '删除成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\ContactController.delete] ' . $e->getMessage());
            return $this->error('删除失败，请稍后重试', 500);
        }
    }

    /**
     * 处理消息
     * 
     * 将消息状态设置为已处理
     * POST /api/contacts/process/{id}
     * 
     * @param int|null $id 消息ID
     * @return JSON 处理结果
     */
    public function process($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询消息
            $contact = $this->contactModel->find($id);

            // 消息不存在
            if (!$contact) {
                return $this->error('消息不存在', 404);
            }

            // 更新状态为已处理
            if (!$this->contactModel->update($id, ['status' => 'processed'])) {
                return $this->error('处理失败', 500);
            }

            return $this->success(null, '处理成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\ContactController.process] ' . $e->getMessage());
            return $this->error('处理失败，请稍后重试', 500);
        }
    }
}
