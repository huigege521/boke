<?php

namespace App\Controllers\Api;

use App\Models\TagModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * 标签管理 API 控制器
 * 
 * 提供标签的 RESTful API 接口，支持完整的 CRUD 操作
 * 所有接口均需要用户登录验证
 * 
 * @package App\Controllers\Api
 */
class TagController extends BaseApiController
{
    /**
     * @var TagModel 标签模型实例
     */
    protected $tagModel;

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
        $this->tagModel = new TagModel();
    }

    /**
     * 获取标签列表
     * 
     * 支持分页，按名称升序排列
     * GET /api/tags
     * 
     * @return JSON 标签列表数据
     */
    public function index()
    {
        try {

            // 获取分页参数
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;

            // 构建查询
            $builder = $this->tagModel->builder();
            $total = $builder->countAllResults(false);

            // 分页查询
            $offset = ($page - 1) * $perPage;
            $tags = $builder->orderBy('name', 'asc')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // 处理返回数据
            foreach ($tags as &$tag) {
                $tag['edit_url'] = base_url('admin/tags/' . $tag['id'] . '/edit');
            }

            return $this->success([
                'list' => $tags,
                'pagination' => [
                    'page' => (int) $page,
                    'per_page' => (int) $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ], '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\TagController.index] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 获取单个标签详情
     * 
     * GET /api/tags/{id}
     * 
     * @param int|null $id 标签ID
     * @return JSON 标签详情数据
     */
    public function show($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询标签
            $tag = $this->tagModel->find($id);

            // 标签不存在
            if (!$tag) {
                return $this->error('标签不存在', 404);
            }

            return $this->success($tag, '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\TagController.show] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 创建新标签
     * 
     * POST /api/tags
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
                'slug' => 'permit_empty|is_unique[tags.slug]',
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
                'slug' => $data['slug'] ?? '',
                'description' => $data['description'] ?? '',
            ];

            // 插入数据库
            $id = $this->tagModel->insert($saveData);

            if (!$id) {
                return $this->error('创建失败', 500);
            }

            return $this->success(['id' => $id], '创建成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\TagController.create] ' . $e->getMessage());
            return $this->error('创建失败，请稍后重试', 500);
        }
    }

    /**
     * 更新标签
     * 
     * PUT /api/tags/{id}
     * 
     * @param int|null $id 标签ID
     * @return JSON 更新结果
     */
    public function update($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询标签
            $tag = $this->tagModel->find($id);

            // 标签不存在
            if (!$tag) {
                return $this->error('标签不存在', 404);
            }

            // 获取请求数据（支持 FormData 和 JSON）
            $data = $this->request->getPost();
            if (empty($data)) {
                $data = $this->request->getJSON(true) ?? [];
            }

            // 验证规则
            $rules = [
                'name' => 'required|min_length[2]|max_length[100]',
                'slug' => 'permit_empty|is_unique[tags.slug,id,' . $id . ']',
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
                'slug' => $data['slug'] ?? '',
                'description' => $data['description'] ?? '',
            ];

            // 更新数据库
            if (!$this->tagModel->update($id, $updateData)) {
                return $this->error('更新失败', 500);
            }

            return $this->success(null, '更新成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\TagController.update] ' . $e->getMessage());
            return $this->error('更新失败，请稍后重试', 500);
        }
    }

    /**
     * 获取热门标签
     * 
     * GET /api/tags/hot
     * 
     * @return JSON 热门标签列表
     */
    public function hot()
    {
        try {
            // 获取热门标签（按文章数降序排列，取前10个）
            $hotTags = $this->tagModel->builder()
                ->where('posts_count >', 0)
                ->orderBy('posts_count', 'desc')
                ->limit(10)
                ->get()
                ->getResultArray();

            return $this->success($hotTags, '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\TagController.hot] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 删除标签
     * 
     * DELETE /api/tags/{id}
     * 
     * @param int|null $id 标签ID
     * @return JSON 删除结果
     */
    public function delete($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询标签
            $tag = $this->tagModel->find($id);

            // 标签不存在
            if (!$tag) {
                return $this->error('标签不存在', 404);
            }

            // 检查是否有文章使用该标签
            if ($tag['posts_count'] > 0) {
                return $this->error('该标签下有文章，无法删除', 400);
            }

            // 删除标签
            if (!$this->tagModel->delete($id)) {
                return $this->error('删除失败', 500);
            }

            // 删除关联表数据
            $db = \Config\Database::connect();
            $db->table('post_tags')->where('tag_id', $id)->delete();

            return $this->success(null, '删除成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\TagController.delete] ' . $e->getMessage());
            return $this->error('删除失败，请稍后重试', 500);
        }
    }
}
