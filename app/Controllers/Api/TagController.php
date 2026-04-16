<?php

namespace App\Controllers\Api;

use App\Models\TagModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * 标签 API 控制器
 * 处理标签的 CRUD 操作
 */
class TagController extends BaseApiController
{
    /**
     * 模型实例
     * @var TagModel
     */
    protected $tagModel;

    /**
     * 构造函数
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->tagModel = new TagModel();
    }

    /**
     * 获取标签列表
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        $tags = $this->tagModel->findAll();
        return $this->success($tags, '获取标签列表成功');
    }

    /**
     * 获取标签详情
     *
     * @param int $id 标签ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function show($id)
    {
        $tag = $this->tagModel->find($id);
        if (!$tag) {
            return $this->notFound('标签不存在');
        }
        return $this->success($tag, '获取标签详情成功');
    }

    /**
     * 创建标签
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function create()
    {
        // 验证请求数据
        $rules = [
            'name' => 'required|min_length[2]|max_length[50]',
            'slug' => 'required|min_length[2]|max_length[50]|alpha_dash'
        ];

        if (!$this->validate($rules)) {
            return $this->validationError($this->validator->getErrors());
        }

        // 准备数据
        $data = [
            'name' => $this->request->getVar('name'),
            'slug' => $this->request->getVar('slug'),
            'description' => $this->request->getVar('description')
        ];

        // 创建标签
        $tagId = $this->tagModel->insert($data);
        if (!$tagId) {
            return $this->error('创建标签失败');
        }

        $tag = $this->tagModel->find($tagId);
        return $this->success($tag, '创建标签成功', 201);
    }

    /**
     * 更新标签
     *
     * @param int $id 标签ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function update($id)
    {
        $tag = $this->tagModel->find($id);
        if (!$tag) {
            return $this->notFound('标签不存在');
        }

        // 验证请求数据
        $rules = [
            'name' => 'required|min_length[2]|max_length[50]',
            'slug' => 'required|min_length[2]|max_length[50]|alpha_dash'
        ];

        if (!$this->validate($rules)) {
            return $this->validationError($this->validator->getErrors());
        }

        // 准备数据
        $data = [
            'name' => $this->request->getVar('name'),
            'slug' => $this->request->getVar('slug'),
            'description' => $this->request->getVar('description')
        ];

        // 更新标签
        if (!$this->tagModel->update($id, $data)) {
            return $this->error('更新标签失败');
        }

        $updatedTag = $this->tagModel->find($id);
        return $this->success($updatedTag, '更新标签成功');
    }

    /**
     * 删除标签
     *
     * @param int $id 标签ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($id)
    {
        $tag = $this->tagModel->find($id);
        if (!$tag) {
            return $this->notFound('标签不存在');
        }

        if (!$this->tagModel->delete($id)) {
            return $this->error('删除标签失败');
        }

        return $this->success(null, '删除标签成功');
    }
}
