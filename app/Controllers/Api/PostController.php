<?php

namespace App\Controllers\Api;

use App\Models\PostModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * 文章 API 控制器
 * 处理文章的 CRUD 操作
 */
class PostController extends BaseApiController
{
    /**
     * 模型实例
     * @var PostModel
     */
    protected $postModel;

    /**
     * 构造函数
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->postModel = new PostModel();
    }

    /**
     * 获取文章列表
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $limit = $this->request->getVar('limit') ?? 10;
        $categoryId = $this->request->getVar('category_id') ?? null;
        $tagId = $this->request->getVar('tag_id') ?? null;
        $search = $this->request->getVar('search') ?? null;

        $offset = ($page - 1) * $limit;
        $posts = $this->postModel->getPublishedPosts($limit, $offset, $categoryId, $tagId, $search);
        $total = $this->postModel->countPublishedPosts($categoryId, $tagId, $search);

        return $this->success([
            'posts' => $posts,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$limit,
                'total' => (int)$total,
                'total_pages' => ceil($total / $limit)
            ]
        ], '获取文章列表成功');
    }

    /**
     * 获取文章详情
     *
     * @param int $id 文章ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function show($id)
    {
        $post = $this->postModel->getPostById($id);
        if (!$post) {
            return $this->notFound('文章不存在');
        }

        // 增加浏览次数
        $this->postModel->incrementViews($id);

        return $this->success($post, '获取文章详情成功');
    }

    /**
     * 创建文章
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function create()
    {
        // 验证请求数据
        $rules = [
            'title' => 'required|min_length[3]|max_length[200]',
            'content' => 'required',
            'category_id' => 'required|is_natural_no_zero',
            'status' => 'required|in_list[draft,published,pending,scheduled]',
            'visibility' => 'required|in_list[public,private]'
        ];

        if (!$this->validate($rules)) {
            return $this->validationError($this->validator->getErrors());
        }

        // 准备数据
        $data = [
            'title' => $this->request->getVar('title'),
            'content' => $this->request->getVar('content'),
            'excerpt' => $this->request->getVar('excerpt') ?? $this->generateExcerpt($this->request->getVar('content')),
            'category_id' => $this->request->getVar('category_id'),
            'status' => $this->request->getVar('status'),
            'visibility' => $this->request->getVar('visibility'),
            'user_id' => 1, // 假设当前用户ID为1，实际应该从认证中获取
            'published_at' => $this->request->getVar('status') == 'published' ? date('Y-m-d H:i:s') : null,
            'scheduled_at' => $this->request->getVar('status') == 'scheduled' ? $this->request->getVar('scheduled_at') : null
        ];

        // 生成slug
        $data['slug'] = url_title($data['title'], '-', true);

        // 处理特色图片
        if ($this->request->getFile('featured_image')) {
            $image = $this->request->getFile('featured_image');
            if ($image->isValid() && !$image->hasMoved()) {
                $fileName = $image->getRandomName();
                $image->move(ROOTPATH . 'public/uploads', $fileName);
                $data['featured_image'] = $fileName;
            }
        }

        // 创建文章
        $postId = $this->postModel->insert($data);
        if (!$postId) {
            return $this->error('创建文章失败');
        }

        // 处理标签
        $tags = $this->request->getVar('tags') ?? [];
        if (!empty($tags)) {
            $this->savePostTags($postId, $tags);
        }

        $post = $this->postModel->getPostById($postId);
        return $this->success($post, '创建文章成功', 201);
    }

    /**
     * 更新文章
     *
     * @param int $id 文章ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function update($id)
    {
        $post = $this->postModel->find($id);
        if (!$post) {
            return $this->notFound('文章不存在');
        }

        // 验证请求数据
        $rules = [
            'title' => 'required|min_length[3]|max_length[200]',
            'content' => 'required',
            'category_id' => 'required|is_natural_no_zero',
            'status' => 'required|in_list[draft,published,pending,scheduled]',
            'visibility' => 'required|in_list[public,private]'
        ];

        if (!$this->validate($rules)) {
            return $this->validationError($this->validator->getErrors());
        }

        // 准备数据
        $data = [
            'title' => $this->request->getVar('title'),
            'content' => $this->request->getVar('content'),
            'excerpt' => $this->request->getVar('excerpt') ?? $this->generateExcerpt($this->request->getVar('content')),
            'category_id' => $this->request->getVar('category_id'),
            'status' => $this->request->getVar('status'),
            'visibility' => $this->request->getVar('visibility')
        ];

        // 生成slug
        $data['slug'] = url_title($data['title'], '-', true);

        // 处理特色图片
        if ($this->request->getFile('featured_image')) {
            $image = $this->request->getFile('featured_image');
            if ($image->isValid() && !$image->hasMoved()) {
                $fileName = $image->getRandomName();
                $image->move(ROOTPATH . 'public/uploads', $fileName);
                $data['featured_image'] = $fileName;
            }
        }

        // 处理发布时间和定时发布时间
        if ($this->request->getVar('status') == 'published') {
            if ($post['status'] != 'published') {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
            $data['scheduled_at'] = null;
        } elseif ($this->request->getVar('status') == 'scheduled') {
            $data['scheduled_at'] = $this->request->getVar('scheduled_at');
            $data['published_at'] = null;
        } else {
            $data['scheduled_at'] = null;
            $data['published_at'] = null;
        }

        // 更新文章
        if (!$this->postModel->update($id, $data)) {
            return $this->error('更新文章失败');
        }

        // 处理标签
        $tags = $this->request->getVar('tags') ?? [];
        $this->updatePostTags($id, $tags);

        $updatedPost = $this->postModel->getPostById($id);
        return $this->success($updatedPost, '更新文章成功');
    }

    /**
     * 删除文章
     *
     * @param int $id 文章ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($id)
    {
        $post = $this->postModel->find($id);
        if (!$post) {
            return $this->notFound('文章不存在');
        }

        if (!$this->postModel->delete($id)) {
            return $this->error('删除文章失败');
        }

        return $this->success(null, '删除文章成功');
    }

    /**
     * 生成摘要
     *
     * @param string $content 文章内容
     * @return string 摘要
     */
    private function generateExcerpt($content)
    {
        $excerpt = strip_tags($content);
        $excerpt = trim($excerpt);
        if (strlen($excerpt) > 150) {
            $excerpt = substr($excerpt, 0, 150) . '...';
        }
        return $excerpt;
    }

    /**
     * 保存文章标签关联
     *
     * @param int $postId 文章ID
     * @param array $tagIds 标签ID数组
     */
    private function savePostTags($postId, $tagIds)
    {
        $db = \Config\Database::connect();
        $data = [];
        foreach ($tagIds as $tagId) {
            $data[] = [
                'post_id' => $postId,
                'tag_id' => $tagId,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($data)) {
            $db->table('post_tags')->insertBatch($data);
        }
    }

    /**
     * 更新文章标签关联
     *
     * @param int $postId 文章ID
     * @param array $tagIds 标签ID数组
     */
    private function updatePostTags($postId, $tagIds)
    {
        $db = \Config\Database::connect();
        $db->table('post_tags')->where('post_id', $postId)->delete();
        $this->savePostTags($postId, $tagIds);
    }
}
