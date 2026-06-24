<?php

namespace App\Controllers\Api;

use App\Models\PostModel;
use App\Models\CategoryModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * 文章管理 API 控制器
 * 
 * 提供文章的 RESTful API 接口，支持完整的 CRUD 操作和批量操作
 * 所有接口均需要用户登录验证
 * 
 * @package App\Controllers\Api
 */
class PostController extends BaseApiController
{
    /**
     * @var PostModel 文章模型实例
     */
    protected $postModel;

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
        $this->postModel = new PostModel();
        $this->categoryModel = new CategoryModel();
    }

    /**
     * 获取文章列表
     * 
     * 支持搜索、筛选、排序和分页
     * GET /api/posts
     * 
     * @return JSON 文章列表数据
     */
    public function index()
    {
        try {
            // 获取查询参数
            $search = $this->request->getGet('search') ?? '';           // 搜索关键词
            $status = $this->request->getGet('status') ?? '';           // 状态筛选
            $categoryId = $this->request->getGet('category') ?? '';     // 分类筛选
            $orderBy = $this->request->getGet('order_by') ?? 'created_at'; // 排序字段
            $orderDirection = $this->request->getGet('order_direction') ?? 'desc'; // 排序方向
            $page = (int) ($this->request->getGet('page') ?? 1);         // 当前页码
            $perPage = (int) ($this->request->getGet('per_page') ?? 20); // 每页数量

            // 构建查询
            $builder = $this->postModel->builder();

            // 搜索条件
            if (!empty($search)) {
                $builder->groupStart()
                    ->like('title', $search)
                    ->orLike('content', $search)
                    ->groupEnd();
            }

            // 状态筛选
            if (!empty($status) && in_array($status, ['draft', 'published', 'pending', 'scheduled'])) {
                $builder->where('status', $status);
            }

            // 分类筛选
            if (!empty($categoryId)) {
                $builder->where('category_id', $categoryId);
            }

            // 排序
            if (in_array($orderBy, ['created_at', 'published_at', 'views', 'comments_count'])) {
                $builder->orderBy($orderBy, $orderDirection);
            }

            // 统计总数
            $total = $builder->countAllResults(false);

            // 分页查询
            $offset = ($page - 1) * $perPage;
            $posts = $builder->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            // 处理返回数据，添加额外字段
            foreach ($posts as &$post) {
                // 获取分类名称
                $category = $post['category_id'] ? $this->categoryModel->find($post['category_id']) : null;
                $post['category_name'] = $category ? $category['name'] : '未分类';

                // 状态文本
                $post['status_text'] = $post['status'] == 'draft' ? '草稿' :
                    ($post['status'] == 'published' ? '已发布' :
                        ($post['status'] == 'scheduled' ? '定时发布' : '待审核'));

                // 可见性文本
                $post['visibility_text'] = $post['visibility'] == 'public' ? '公开' : '私有';

                // 编辑链接
                $post['edit_url'] = base_url('admin/posts/' . $post['id'] . '/edit');

                // 获取文章标签
                $postTags = $this->postModel->getPostTags($post['id']);
                $post['tags'] = $postTags;
            }

            // 返回成功响应
            return $this->success([
                'list' => $posts,
                'pagination' => [
                    'page' => (int) $page,
                    'per_page' => (int) $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ],
                'filters' => [
                    'categories' => $this->categoryModel->findAll()
                ]
            ], '获取成功');

        } catch (\Exception $e) {
            // 记录错误日志
            log_message('error', '[Api\PostController.index] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 获取单篇文章详情
     * 
     * GET /api/posts/{id}
     * 
     * @param int|null $id 文章ID
     * @return JSON 文章详情数据
     */
    public function show($id = null)
    {
        try {
            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询文章
            $post = $this->postModel->find($id);

            // 文章不存在
            if (!$post) {
                return $this->error('文章不存在', 404);
            }

            // 添加分类名称和标签
            $post['category_name'] = $post['category_id'] ?
                $this->categoryModel->find($post['category_id'])['name'] : '未分类';
            $post['tags'] = $this->postModel->getPostTags($id);

            return $this->success($post, '获取成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\PostController.show] ' . $e->getMessage());
            return $this->error('数据加载失败，请稍后重试', 500);
        }
    }

    /**
     * 创建新文章
     * 
     * POST /api/posts
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

            // 验证必填字段
            if (empty($data['title'])) {
                return $this->error('标题不能为空', 400);
            }
            if (empty($data['content'])) {
                return $this->error('内容不能为空', 400);
            }

            // 处理别名（slug）
            $slug = $data['slug'] ?? '';
            if (empty($slug)) {
                $slug = url_title($data['title'], '-', true);
            }

            // 处理特色图片
            $featuredImageName = null;
            $featuredImageId = null;
            
            // 1. 先检查是否上传了新文件
            $imageFile = $this->request->getFile('featured_image');
            if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
                // 生成与媒体库一致的文件名和路径
                $extension = $imageFile->getClientExtension();
                $datePath = date('Ymd');
                $fileName = $datePath . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

                // 确保目录存在
                $uploadPath = FCPATH . 'uploads/' . $datePath;
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $imageFile->move($uploadPath, $fileName);
                $featuredImageName = $datePath . '/' . $fileName;
                
                // 同时保存到媒体库
                $mediaModel = new \App\Models\MediaModel();
                $mediaData = [
                    'filename' => $fileName,
                    'original_name' => $imageFile->getClientName(),
                    'file_path' => $uploadPath . '/' . $fileName,
                    'file_url' => base_url('uploads/' . $datePath . '/' . $fileName),
                    'file_type' => $this->getFileType($imageFile->getClientMimeType()),
                    'file_size' => $imageFile->getSize(),
                    'mime_type' => $imageFile->getClientMimeType(),
                    'extension' => $extension,
                    'user_id' => session()->get('user_id') ?: 1,
                    'is_image' => 1,
                ];
                
                $mediaId = $mediaModel->insert($mediaData);
                if ($mediaId) {
                    $featuredImageId = $mediaId;
                }
            }
            
            // 2. 如果没有上传新文件，检查是否从媒体库选择了图片
            if (!$featuredImageId && !empty($data['featured_image_from_media_id'])) {
                $featuredImageId = (int)$data['featured_image_from_media_id'];
                
                // 获取媒体库中的图片URL
                $mediaModel = new \App\Models\MediaModel();
                $media = $mediaModel->find($featuredImageId);
                if ($media) {
                    $featuredImageName = str_replace(base_url('uploads/'), '', $media['file_url']);
                }
            }

            // 准备保存数据
            $saveData = [
                'title' => $data['title'],
                'slug' => $slug,
                'content' => $data['content'],
                'excerpt' => $data['excerpt'] ?? '',
                'category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : 0,
                'status' => $data['status'] ?? 'draft',
                'visibility' => $data['visibility'] ?? 'public',
                'published_at' => $data['published_at'] ?? null,
                'user_id' => session()->get('user_id') ?: 1,
                // 特色图片
                'featured_image' => $featuredImageName,
                'featured_image_id' => $featuredImageId,
                // SEO元数据
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
            ];

            // 插入数据库
            $id = $this->postModel->insert($saveData);

            if (!$id) {
                $dbError = $this->postModel->db->error();
                log_message('error', '[Api\PostController.create] DB Error: ' . print_r($dbError, true));
                return $this->error('创建失败: ' . ($dbError['message'] ?? '数据库错误'), 500);
            }

            // 更新分类文章计数
            if (!empty($saveData['category_id'])) {
                $this->categoryModel->incrementPostCount($saveData['category_id']);
            }

            // 处理标签关联
            if (!empty($data['tags'])) {
                $tags = json_decode(htmlspecialchars_decode($data['tags']), true);
                $this->postModel->setPostTags($id, $tags);
            }

            return $this->success(['id' => $id], '创建成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\PostController.create] Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return $this->error('创建失败，请稍后重试: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 更新文章
     * 
     * PUT /api/posts/{id}
     * 
     * @param int|null $id 文章ID
     * @return JSON 更新结果
     */
    public function update($id = null)
    {
        try {
            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询文章
            $post = $this->postModel->find($id);

            // 文章不存在
            if (!$post) {
                return $this->error('文章不存在', 404);
            }

            // 获取请求数据（支持 FormData 和 JSON）
            $postData = $this->request->getPost();
            if (empty($postData)) {
                $postData = $this->request->getJSON(true) ?? [];
            }

            // 验证必填字段
            if (empty($postData['title'])) {
                return $this->error('标题不能为空', 400);
            }
            if (empty($postData['content'])) {
                return $this->error('内容不能为空', 400);
            }

            // 处理别名
            $slug = $postData['slug'] ?? '';
            if (empty($slug)) {
                $slug = url_title($postData['title'], '-', true);
            }

            // 处理特色图片
            $featuredImageName = $post['featured_image'] ?? null;
            $featuredImageId = $post['featured_image_id'] ?? null;
            
            // 1. 先检查是否上传了新文件
            $imageFile = $this->request->getFile('featured_image');
            if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
                // 生成与媒体库一致的文件名和路径
                $extension = $imageFile->getClientExtension();
                $datePath = date('Ymd');
                $fileName = $datePath . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

                // 确保目录存在
                $uploadPath = FCPATH . 'uploads/' . $datePath;
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $imageFile->move($uploadPath, $fileName);
                $featuredImageName = $datePath . '/' . $fileName;
                
                // 同时保存到媒体库
                $mediaModel = new \App\Models\MediaModel();
                $mediaData = [
                    'filename' => $fileName,
                    'original_name' => $imageFile->getClientName(),
                    'file_path' => $uploadPath . '/' . $fileName,
                    'file_url' => base_url('uploads/' . $datePath . '/' . $fileName),
                    'file_type' => $this->getFileType($imageFile->getClientMimeType()),
                    'file_size' => $imageFile->getSize(),
                    'mime_type' => $imageFile->getClientMimeType(),
                    'extension' => $extension,
                    'user_id' => session()->get('user_id') ?: 1,
                    'is_image' => 1,
                ];
                
                $mediaId = $mediaModel->insert($mediaData);
                if ($mediaId) {
                    $featuredImageId = $mediaId;
                }
            }
            
            // 2. 如果没有上传新文件，检查是否从媒体库选择了图片
            if (!$featuredImageId && !empty($postData['featured_image_from_media_id'])) {
                $featuredImageId = (int)$postData['featured_image_from_media_id'];
                
                // 获取媒体库中的图片URL
                $mediaModel = new \App\Models\MediaModel();
                $media = $mediaModel->find($featuredImageId);
                if ($media) {
                    $featuredImageName = str_replace(base_url('uploads/'), '', $media['file_url']);
                }
            }
            
            // 如果明确传入了空值或特定标识，可能意味着要移除图片（视具体业务逻辑而定，这里暂保持原有或新设置的值）
            // 如果需要支持删除图片，可以在此处添加逻辑，例如检查 $postData['remove_featured_image']

            // 准备更新数据
            $updateData = [
                'title' => $postData['title'],
                'slug' => $slug,
                'content' => $postData['content'],
                'excerpt' => $postData['excerpt'] ?? '',
                'category_id' => !empty($postData['category_id']) ? (int) $postData['category_id'] : 0,
                'status' => $postData['status'] ?? 'draft',
                'visibility' => $postData['visibility'] ?? 'public',
                'published_at' => $postData['published_at'] ?? null,
                // 特色图片
                'featured_image' => $featuredImageName,
                'featured_image_id' => $featuredImageId,
                // SEO元数据
                'meta_title' => $postData['meta_title'] ?? null,
                'meta_description' => $postData['meta_description'] ?? null,
                'meta_keywords' => $postData['meta_keywords'] ?? null,
            ];

            // 更新数据库
            if (!$this->postModel->update($id, $updateData)) {
                $dbError = $this->postModel->db->error();
                log_message('error', '[Api\PostController.update] DB Error: ' . print_r($dbError, true));
                return $this->error('更新失败: ' . ($dbError['message'] ?? '数据库错误'), 500);
            }

            // 如果分类发生变化，更新分类文章计数
            $newCategoryId = !empty($updateData['category_id']) ? $updateData['category_id'] : 0;
            $oldCategoryId = !empty($post['category_id']) ? $post['category_id'] : 0;
            if ($newCategoryId != $oldCategoryId) {
                // 减少旧分类的文章计数
                if ($oldCategoryId > 0) {
                    $this->categoryModel->decrementPostCount($oldCategoryId);
                }
                // 增加新分类的文章计数
                if ($newCategoryId > 0) {
                    $this->categoryModel->incrementPostCount($newCategoryId);
                }
            }

            // 更新标签关联
            if (isset($postData['tags']) && !empty($postData['tags'])) {
                $tags = json_decode(htmlspecialchars_decode($postData['tags']), true);
                $this->postModel->setPostTags($id, $tags);
            }

            return $this->success(null, '更新成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\PostController.update] Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return $this->error('更新失败，请稍后重试: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 删除文章
     * 
     * DELETE /api/posts/{id}
     * 
     * @param int|null $id 文章ID
     * @return JSON 删除结果
     */
    public function delete($id = null)
    {
        try {
            // 登录验证（使用基类封装的方法）
            // 参数验证
            if (!$id) {
                return $this->error('参数错误', 400);
            }

            // 查询文章
            $post = $this->postModel->find($id);

            // 文章不存在
            if (!$post) {
                return $this->error('文章不存在', 404);
            }

            // 获取文章的分类ID和标签ID
            $categoryId = !empty($post['category_id']) ? $post['category_id'] : 0;
            $tags = $this->postModel->getPostTags($id);
            $tagIds = array_column($tags, 'id');

            // 删除文章
            if (!$this->postModel->delete($id)) {
                return $this->error('删除失败', 500);
            }

            // 更新分类文章计数
            if ($categoryId > 0) {
                $this->categoryModel->decrementPostCount($categoryId);
            }

            // 更新标签文章计数
            foreach ($tagIds as $tagId) {
                $this->postModel->decrementTagPostCount($tagId);
            }

            return $this->success(null, '删除成功');

        } catch (\Exception $e) {
            log_message('error', '[Api\PostController.delete] ' . $e->getMessage());
            return $this->error('删除失败，请稍后重试', 500);
        }
    }

    /**
     * 批量操作
     * 
     * 支持批量发布、设为草稿、设为待审核、批量删除
     * POST /api/posts/batch
     * 
     * @return JSON 操作结果
     */
    public function batch()
    {
        try {
            // 获取请求数据
            $data = $this->request->getJSON(true);
            $action = $data['action'] ?? '';  // 操作类型
            $ids = $data['ids'] ?? [];        // 文章ID数组

            // 参数验证
            if (empty($action) || empty($ids)) {
                return $this->error('参数错误', 400);
            }

            // 过滤有效ID
            $ids = array_filter($ids, function ($id) {
                return is_numeric($id) && $id > 0;
            });

            // 无有效ID
            if (empty($ids)) {
                return $this->error('无效的ID', 400);
            }

            $count = count($ids);

            // 根据操作类型执行对应操作
            switch ($action) {
                case 'publish':
                    $this->postModel->whereIn('id', $ids)->update(['status' => 'published']);
                    return $this->success(['count' => $count], "成功发布 {$count} 篇文章");

                case 'draft':
                    $this->postModel->whereIn('id', $ids)->update(['status' => 'draft']);
                    return $this->success(['count' => $count], "成功将 {$count} 篇文章设为草稿");

                case 'pending':
                    $this->postModel->whereIn('id', $ids)->update(['status' => 'pending']);
                    return $this->success(['count' => $count], "成功将 {$count} 篇文章设为待审核");

                case 'delete':
                    $this->postModel->whereIn('id', $ids)->delete();
                    return $this->success(['count' => $count], "成功删除 {$count} 篇文章");

                default:
                    return $this->error('未知的操作', 400);
            }

        } catch (\Exception $e) {
            log_message('error', '[Api\PostController.batch] ' . $e->getMessage());
            return $this->error('操作失败，请稍后重试', 500);
        }
    }

    /**
     * 获取文件类型
     * 
     * @param string $mimeType MIME类型
     * @return string 文件类型
     */
    private function getFileType(string $mimeType): string
    {
        if (strpos($mimeType, 'image/') === 0) {
            return 'image';
        } elseif (strpos($mimeType, 'video/') === 0) {
            return 'video';
        } elseif (strpos($mimeType, 'audio/') === 0) {
            return 'audio';
        } elseif ($mimeType === 'application/pdf') {
            return 'document';
        } else {
            return 'other';
        }
    }
}
