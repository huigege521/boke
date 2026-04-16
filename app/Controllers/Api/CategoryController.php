<?php

namespace App\Controllers\Api;

use App\Models\CategoryModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * 分类 API 控制器
 * 处理分类的 CRUD 操作
 */
class CategoryController extends BaseApiController
{
    /**
     * 模型实例
     * @var CategoryModel
     */
    protected $categoryModel;

    /**
     * 构造函数
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->categoryModel = new CategoryModel();
    }

    /**
     * 获取分类列表
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        $categories = $this->categoryModel->getCategoriesWithCount();
        return $this->success($categories, '获取分类列表成功');
    }

    /**
     * 获取分类详情
     *
     * @param int $id 分类ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function show($id)
    {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            return $this->notFound('分类不存在');
        }
        return $this->success($category, '获取分类详情成功');
    }

    /**
     * 创建分类
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function create()
    {
        // 验证请求数据
        $rules = [
            'name' => 'required|min_length[2]|max_length[50]',
            'slug' => 'required|min_length[2]|max_length[50]|alpha_dash',
            'parent_id' => 'permit_empty|is_natural_no_zero'
        ];

        if (!$this->validate($rules)) {
            return $this->validationError($this->validator->getErrors());
        }

        // 准备数据
        $data = [
            'name' => $this->request->getVar('name'),
            'slug' => $this->request->getVar('slug'),
            'parent_id' => $this->request->getVar('parent_id') ?? null,
            'description' => $this->request->getVar('description'),
            'icon' => $this->request->getVar('icon')
        ];

        // 创建分类
        $categoryId = $this->categoryModel->insert($data);
        if (!$categoryId) {
            return $this->error('创建分类失败');
        }

        $category = $this->categoryModel->find($categoryId);
        return $this->success($category, '创建分类成功', 201);
    }

    /**
     * 更新分类
     *
     * @param int $id 分类ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function update($id)
    {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            return $this->notFound('分类不存在');
        }

        // 验证请求数据
        $rules = [
            'name' => 'required|min_length[2]|max_length[50]',
            'slug' => 'required|min_length[2]|max_length[50]|alpha_dash',
            'parent_id' => 'permit_empty|is_natural_no_zero'
        ];

        if (!$this->validate($rules)) {
            return $this->validationError($this->validator->getErrors());
        }

        // 准备数据
        $data = [
            'name' => $this->request->getVar('name'),
            'slug' => $this->request->getVar('slug'),
            'parent_id' => $this->request->getVar('parent_id') ?? null,
            'description' => $this->request->getVar('description'),
            'icon' => $this->request->getVar('icon')
        ];

        // 更新分类
        if (!$this->categoryModel->update($id, $data)) {
            return $this->error('更新分类失败');
        }

        $updatedCategory = $this->categoryModel->find($id);
        return $this->success($updatedCategory, '更新分类成功');
    }

    /**
     * 删除分类
     *
     * @param int $id 分类ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($id)
    {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            return $this->notFound('分类不存在');
        }

        if (!$this->categoryModel->delete($id)) {
            return $this->error('删除分类失败');
        }

        return $this->success(null, '删除分类成功');
    }
}
