<?php

namespace App\Controllers\Api;

use App\Models\LinkModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * 友情链接管理 API 控制器
 * 
 * 提供友情链接的 RESTful API 接口，支持完整的 CRUD 操作
 * 所有接口均需要用户登录验证
 * 
 * @package App\Controllers\Api
 */
class LinkController extends BaseApiController
{
    /**
     * @var LinkModel 链接模型实例
     */
    protected $linkModel;

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
        $this->linkModel = new LinkModel();
    }

    /**
     * 获取友情链接列表
     * 
     * 支持分页，按排序字段升序排列
     * GET /api/links
     * 
     * @return JSON 链接列表数据
     */
    public function index()
    {
        try {

            // 获取分页参数
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;

            // 构建查询
            $builder = $this->linkModel->builder();
            $total = $builder->countAllResults(false);

            // 分页查询
            $offset = ($page - 1) * $perPage;
            $links = $builder->orderBy('sort_order', 'asc')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // 处理返回数据
            foreach ($links as &$link) {
                $link['edit_url'] = base_url('admin/links/' . $link['id'] . '/edit');
            }

            return $this->success([
                'list' => $links,
                'pagination' => [
                    'page' => (int) $page,
                    'per_page' => (int) $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ], '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\LinkController.index] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 获取单个链接详情
     * 
     * GET /api/links/{id}
     * 
     * @param int|null $id 链接ID
     * @return JSON 链接详情数据
     */
    public function show($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询链接
            $link = $this->linkModel->find($id);

            // 链接不存在
            if (!$link) {
                return $this->error('链接不存在', 404);
            }

            return $this->success($link, '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\LinkController.show] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 创建新链接
     * 
     * POST /api/links
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
                'name' => 'required|min_length[2]|max_length[100]',
                'url' => 'required|valid_url',
            ];

            // 数据验证
            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                $errorMessage = '验证失败：' . implode('；', $errors);
                return $this->error($errorMessage, 400);
            }

            // 准备保存数据
            $saveData = [
                'name' => $data['name'],
                'url' => $data['url'],
                'description' => $data['description'] ?? '',
                'logo' => $data['logo'] ?? '',
                'order' => $data['order'] ?? 0,
                'status' => $data['status'] ?? 'active',
            ];

            // 插入数据库
            $id = $this->linkModel->insert($saveData);

            if (!$id) {
                return $this->error('创建失败', 500);
            }

            return $this->success(['id' => $id], '创建成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\LinkController.create] ' . $e->getMessage());
            return $this->error('创建失败，请稍后重试', 500);
        }
    }

    /**
     * 更新链接
     * 
     * PUT /api/links/{id}
     * 
     * @param int|null $id 链接ID
     * @return JSON 更新结果
     */
    public function update($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询链接
            $link = $this->linkModel->find($id);

            // 链接不存在
            if (!$link) {
                return $this->error('链接不存在', 404);
            }

            // 获取请求数据（支持 FormData 和 JSON）
            $data = $this->request->getPost();
            if (empty($data)) {
                $data = $this->request->getJSON(true) ?? [];
            }

            // 验证规则
            $rules = [
                'name' => 'required|min_length[2]|max_length[100]',
                'url' => 'required|valid_url',
            ];

            // 数据验证
            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                $errorMessage = '验证失败：' . implode('；', $errors);
                return $this->error($errorMessage, 400);
            }

            // 准备更新数据
            $updateData = [
                'name' => $data['name'],
                'url' => $data['url'],
                'description' => $data['description'] ?? '',
                'logo' => $data['logo'] ?? '',
                'order' => $data['order'] ?? 0,
                'status' => $data['status'] ?? 'active',
            ];

            // 更新数据库
            if (!$this->linkModel->update($id, $updateData)) {
                return $this->error('更新失败', 500);
            }

            return $this->success(null, '更新成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\LinkController.update] ' . $e->getMessage());
            return $this->error('更新失败，请稍后重试', 500);
        }
    }

    /**
     * 删除链接
     * 
     * DELETE /api/links/{id}
     * 
     * @param int|null $id 链接ID
     * @return JSON 删除结果
     */
    public function delete($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询链接
            $link = $this->linkModel->find($id);

            // 链接不存在
            if (!$link) {
                return $this->error('链接不存在', 404);
            }

            // 删除链接
            if (!$this->linkModel->delete($id)) {
                return $this->error('删除失败', 500);
            }

            return $this->success(null, '删除成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\LinkController.delete] ' . $e->getMessage());
            return $this->error('删除失败，请稍后重试', 500);
        }
    }
}
