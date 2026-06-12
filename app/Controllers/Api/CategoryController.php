<?php

namespace App\Controllers\Api;

use App\Models\CategoryModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * 分类管理 API 控制器
 * 
 * 提供分类的 RESTful API 接口，支持完整的 CRUD 操作
 * 所有接口均需要用户登录验证
 * 
 * @package App\Controllers\Api
 */
class CategoryController extends BaseApiController
{
    /**
     * @var CategoryModel 分类模型实例
     */
    protected $categoryModel;

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
        $this->categoryModel = new CategoryModel();
    }

    /**
     * 获取分类列表
     * 
     * 支持分页，按排序字段升序排列
     * GET /api/categories
     * 
     * @return JSON 分类列表数据
     */
    public function index()
    {
        try {

            // 获取分页参数
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 20;

            // 构建查询
            $builder = $this->categoryModel->builder();
            $total = $builder->countAllResults(false);

            // 分页查询
            $offset = ($page - 1) * $perPage;
            $categories = $builder->orderBy('order', 'asc')
                ->orderBy('id', 'asc')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // 处理返回数据
            foreach ($categories as &$category) {
                // 获取父分类名称
                $category['parent_name'] = $category['parent_id'] ?
                    $this->categoryModel->find($category['parent_id'])['name'] : '无';
                // 编辑链接
                $category['edit_url'] = base_url('admin/categories/' . $category['id'] . '/edit');
            }

            return $this->success([
                'list' => $categories,
                'pagination' => [
                    'page' => (int) $page,
                    'per_page' => (int) $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ], '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\CategoryController.index] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 获取单个分类详情
     * 
     * GET /api/categories/{id}
     * 
     * @param int|null $id 分类ID
     * @return JSON 分类详情数据
     */
    public function show($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询分类
            $category = $this->categoryModel->find($id);

            // 分类不存在
            if (!$category) {
                return $this->error('分类不存在', 404);
            }

            return $this->success($category, '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\CategoryController.show] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 创建新分类
     * 
     * POST /api/categories
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
                'slug' => 'permit_empty|is_unique[categories.slug]',
            ];

            // 数据验证
            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                $errorMessage = '验证失败：' . implode('；', $errors);
                return $this->error($errorMessage, 400);
            }

            // 处理别名（slug）
            $slug = $data['slug'] ?? '';
            if (empty($slug)) {
                $slug = url_title($data['name'], '-', true);
            }

            // 准备保存数据
            $parentId = !empty($data['parent_id']) && (int) $data['parent_id'] > 0 ? (int) $data['parent_id'] : null;
            $saveData = [
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? '',
                'parent_id' => $parentId,
                'order' => !empty($data['order']) ? (int) $data['order'] : 0,
            ];

            // 插入数据库
            $id = $this->categoryModel->insert($saveData);

            if (!$id) {
                $dbError = $this->categoryModel->db->error();
                log_message('error', '[Api\CategoryController.create] DB Error: ' . print_r($dbError, true));
                return $this->error('创建失败: ' . ($dbError['message'] ?? '数据库错误'), 500);
            }

            return $this->success(['id' => $id], '创建成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\CategoryController.create] Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return $this->error('创建失败，请稍后重试: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 更新分类
     * 
     * PUT /api/categories/{id}
     * 
     * @param int|null $id 分类ID
     * @return JSON 更新结果
     */
    public function update($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询分类
            $category = $this->categoryModel->find($id);

            // 分类不存在
            if (!$category) {
                return $this->error('分类不存在', 404);
            }

            // 获取请求数据（支持 FormData 和 JSON）
            $data = $this->request->getPost();
            if (empty($data)) {
                $data = $this->request->getJSON(true) ?? [];
            }

            // 验证规则
            $rules = [
                'name' => 'required|min_length[2]|max_length[100]',
                'slug' => 'permit_empty|is_unique[categories.slug,id,' . $id . ']',
            ];

            // 数据验证
            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                $errorMessage = '验证失败：' . implode('；', $errors);
                return $this->error($errorMessage, 400);
            }

            // 处理别名（slug）
            $slug = $data['slug'] ?? '';
            if (empty($slug)) {
                $slug = url_title($data['name'], '-', true);
            }

            // 准备更新数据
            $parentId = !empty($data['parent_id']) && (int) $data['parent_id'] > 0 ? (int) $data['parent_id'] : null;
            $updateData = [
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? '',
                'parent_id' => $parentId,
                'order' => !empty($data['order']) ? (int) $data['order'] : 0,
            ];

            // 更新数据库
            if (!$this->categoryModel->update($id, $updateData)) {
                $dbError = $this->categoryModel->db->error();
                log_message('error', '[Api\CategoryController.update] DB Error: ' . print_r($dbError, true));
                return $this->error('更新失败: ' . ($dbError['message'] ?? '数据库错误'), 500);
            }

            return $this->success(null, '更新成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\CategoryController.update] Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return $this->error('更新失败，请稍后重试: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 删除分类
     * 
     * DELETE /api/categories/{id}
     * 
     * @param int|null $id 分类ID
     * @return JSON 删除结果
     */
    public function delete($id = null)
    {
        try {

            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询分类
            $category = $this->categoryModel->find($id);

            // 分类不存在
            if (!$category) {
                return $this->error('分类不存在', 404);
            }

            // 检查是否有子分类
            if ($this->categoryModel->where('parent_id', $id)->countAllResults() > 0) {
                return $this->error('该分类下有子分类，无法删除', 400);
            }

            // 检查是否有文章
            if ($category['posts_count'] > 0) {
                return $this->error('该分类下有文章，无法删除', 400);
            }

            // 删除分类
            if (!$this->categoryModel->delete($id)) {
                return $this->error('删除失败', 500);
            }

            return $this->success(null, '删除成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\CategoryController.delete] ' . $e->getMessage());
            return $this->error('删除失败，请稍后重试', 500);
        }
    }
}
