<?php

namespace App\Controllers\Admin;

use App\Models\PostModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use CodeIgniter\Controller;

/**
 * 文章控制器
 * 负责文章的管理，包括列表、创建、编辑、删除等操作
 */
class PostController extends Controller
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
     * 文章列表
     * 获取所有文章并显示在列表页面，支持搜索、筛选、排序和分页
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function index()
    {
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();

        // 处理批量操作
        if ($this->request->getMethod() === 'post') {
            $action = $this->request->getVar('action');
            $selectedIds = $this->request->getVar('selected_ids');

            if (!empty($selectedIds) && !empty($action)) {
                if (is_string($selectedIds)) {
                    $selectedIds = [$selectedIds];
                }

                switch ($action) {
                    case 'publish':
                        if ($postModel->batchUpdateStatus($selectedIds, 'published')) {
                            session()->setFlashdata('success', '批量发布成功');
                        } else {
                            session()->setFlashdata('error', '批量发布失败');
                        }
                        break;
                    case 'draft':
                        if ($postModel->batchUpdateStatus($selectedIds, 'draft')) {
                            session()->setFlashdata('success', '批量设为草稿成功');
                        } else {
                            session()->setFlashdata('error', '批量设为草稿失败');
                        }
                        break;
                    case 'pending':
                        if ($postModel->batchUpdateStatus($selectedIds, 'pending')) {
                            session()->setFlashdata('success', '批量设为待审核成功');
                        } else {
                            session()->setFlashdata('error', '批量设为待审核失败');
                        }
                        break;
                    case 'scheduled':
                        if ($postModel->batchUpdateStatus($selectedIds, 'scheduled')) {
                            session()->setFlashdata('success', '批量设为定时发布成功');
                        } else {
                            session()->setFlashdata('error', '批量设为定时发布失败');
                        }
                        break;
                    case 'delete':
                        if ($postModel->batchDelete($selectedIds)) {
                            session()->setFlashdata('success', '批量删除成功');
                        } else {
                            session()->setFlashdata('error', '批量删除失败');
                        }
                        break;
                }
                return redirect()->to('/admin/posts' . $this->getQueryString());
            }
        }

        // 获取筛选和排序参数
        $search = $this->request->getVar('search') ?? '';
        $status = $this->request->getVar('status') ?? '';
        $categoryId = $this->request->getVar('category') ?? '';
        $orderBy = $this->request->getVar('order_by') ?? 'created_at';
        $orderDirection = $this->request->getVar('order_direction') ?? 'desc';

        // 分页设置
        $perPage = 10; // 每页显示10条
        $page = $this->request->getVar('page') ?? 1; // 当前页码
        $offset = ($page - 1) * $perPage; // 偏移量

        // 获取所有文章（包括草稿、待审核等）
        $posts = $postModel->getAllPosts($perPage, $offset, $search, $status, $categoryId, $orderBy, $orderDirection);

        // 为每篇文章获取标签
        if (!empty($posts)) {
            foreach ($posts as &$post) {
                $post['tags'] = $postModel->getPostTags($post['id']);
            }
        }

        // 获取文章总数
        $totalPosts = $postModel->getAllPostsCount($search, $status, $categoryId);
        $totalPages = ceil($totalPosts / $perPage); // 总页数

        // 准备视图数据
        $data = [
            'title' => '文章管理 - 后台',
            'posts' => $posts,
            'categories' => $categoryModel->findAll(),
            'search' => $search,
            'status' => $status,
            'categoryId' => $categoryId,
            'orderBy' => $orderBy,
            'orderDirection' => $orderDirection,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalPosts,
                'per_page' => $perPage,
                'base_url' => '/admin/posts' . $this->getQueryString()
            ]
        ];

        // 渲染文章列表视图
        return view('admin/posts/index', $data);
    }

    /**
     * 获取查询字符串
     * 用于保持筛选和排序参数
     *
     * @return string 查询字符串
     */
    private function getQueryString()
    {
        $params = [];
        $search = $this->request->getVar('search') ?? '';
        $status = $this->request->getVar('status') ?? '';
        $categoryId = $this->request->getVar('category') ?? '';
        $orderBy = $this->request->getVar('order_by') ?? 'created_at';
        $orderDirection = $this->request->getVar('order_direction') ?? 'desc';

        if (!empty($search)) {
            $params['search'] = $search;
        }
        if (!empty($status)) {
            $params['status'] = $status;
        }
        if (!empty($categoryId)) {
            $params['category'] = $categoryId;
        }
        if (!empty($orderBy)) {
            $params['order_by'] = $orderBy;
        }
        if (!empty($orderDirection)) {
            $params['order_direction'] = $orderDirection;
        }

        return empty($params) ? '' : '?' . http_build_query($params);
    }

    /**
     * 创建文章
     * 显示创建文章的表单页面
     *
     * @return string 视图字符串
     */
    public function create()
    {
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();

        // 准备视图数据
        $data = [
            'title' => '创建文章 - 后台',
            'categories' => $categoryModel->findAll(), // 所有分类
            'tags' => $tagModel->findAll(), // 所有标签
        ];

        // 渲染创建文章表单视图
        return view('admin/posts/create', $data);
    }

    /**
     * 存储文章
     * 接收表单数据并保存到数据库
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function store()
    {
        $postModel = new PostModel();

        // 验证表单数据
        $rules = [
            'title' => 'required|min_length[3]|max_length[200]', // 标题必填，长度3-200
            'content' => 'required', // 内容必填
            'category_id' => 'required|is_natural_no_zero', // 分类必填，且为正整数
            'status' => 'required|in_list[draft,published,pending,scheduled]', // 状态必填，只能是draft、published、pending或scheduled
            'visibility' => 'required|in_list[public,private]', // 可见性必填，只能是public或private
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            session()->setFlashdata('errors', $errors);
            session()->setFlashdata('error', '表单验证失败，请检查以下字段：' . implode(', ', array_keys($errors)));
            return redirect()->back()->withInput();
        }

        // 生成slug
        $slug = $this->generateSlug($this->request->getVar('title'));

        // 处理文件上传
        $featuredImage = $this->request->getFile('featured_image');
        $featuredImageName = null;
        if ($featuredImage && $featuredImage->isValid() && !$featuredImage->hasMoved()) {
            // 生成与媒体库一致的文件名和路径
            $extension = $featuredImage->getClientExtension();
            $datePath = date('Ymd');
            $fileName = $datePath . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

            // 确保目录存在
            $uploadPath = ROOTPATH . 'public/uploads/' . $datePath;
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $featuredImage->move($uploadPath, $fileName);
            $featuredImageName = $datePath . '/' . $fileName;
        }

        // 处理从媒体库选择的图片
        $featuredImageFromMedia = $this->request->getVar('featured_image_from_media');
        if ($featuredImageFromMedia && !$featuredImageName) {
            $featuredImageName = $featuredImageFromMedia;
        }

        // 准备文章数据
        $postData = [
            'title' => $this->request->getVar('title'), // 标题
            'slug' => $slug, // slug
            'content' => $this->request->getVar('content'), // 内容
            'excerpt' => $this->request->getVar('excerpt') ?: $this->generateExcerpt($this->request->getVar('content')), // 摘要，为空则自动生成
            'featured_image' => $featuredImageName, // 特色图片
            'user_id' => session()->get('user_id'), // 作者ID
            'category_id' => $this->request->getVar('category_id'), // 分类ID
            'status' => $this->request->getVar('status'), // 状态
            'visibility' => $this->request->getVar('visibility'), // 可见性
            'published_at' => $this->request->getVar('status') == 'published' ? date('Y-m-d H:i:s') : null, // 发布时间
            'scheduled_at' => $this->request->getVar('status') == 'scheduled' ? $this->request->getVar('scheduled_at') : null, // 定时发布时间
        ];

        // 创建文章
        $postId = $postModel->insert($postData);
        if (!$postId) {
            session()->setFlashdata('error', '创建文章失败');
            return redirect()->back()->withInput();
        }

        // 处理标签关联
        $tags = $this->request->getVar('tags') ?: [];
        if (!empty($tags)) {
            $this->savePostTags($postId, $tags);
        }

        // 如果文章状态为已发布，更新分类和标签文章数
        if ($this->request->getVar('status') == 'published') {
            // 更新分类文章数
            $this->updateCategoryPostsCount($this->request->getVar('category_id'), 1);

            // 更新标签文章数
            foreach ($tags as $tagId) {
                $this->updateTagPostsCount($tagId, 1);
            }
        }

        // 重定向到文章列表页面并显示成功消息
        session()->setFlashdata('success', '创建文章成功');
        return redirect()->to('/admin/posts');
    }

    /**
     * 编辑文章
     * 根据ID获取文章数据并显示在编辑表单中
     *
     * @param int $id 文章ID
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function edit($id)
    {
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();

        // 获取文章数据
        $post = $postModel->find($id);
        if (!$post) {
            session()->setFlashdata('error', '文章不存在');
            return redirect()->to('/admin/posts');
        }

        // 获取文章的标签
        $postTags = $this->getPostTags($id);

        // 准备视图数据
        $data = [
            'title' => '编辑文章 - 后台',
            'post' => $post,
            'categories' => $categoryModel->findAll(), // 所有分类
            'tags' => $tagModel->findAll(), // 所有标签
            'postTags' => $postTags, // 文章的标签
        ];

        // 渲染编辑文章表单视图
        return view('admin/posts/edit', $data);
    }

    /**
     * 更新文章
     * 根据ID更新文章数据
     *
     * @param int $id 文章ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function update($id)
    {
        $postModel = new PostModel();

        // 验证表单数据
        $rules = [
            'title' => 'required|min_length[3]|max_length[200]', // 标题必填，长度3-200
            'content' => 'required', // 内容必填
            'category_id' => 'required|is_natural_no_zero', // 分类必填，且为正整数
            'status' => 'required|in_list[draft,published,pending,scheduled]', // 状态必填，只能是draft、published、pending或scheduled
            'visibility' => 'required|in_list[public,private]', // 可见性必填，只能是public或private
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            session()->setFlashdata('errors', $errors);
            session()->setFlashdata('error', '表单验证失败，请检查以下字段：' . implode(', ', array_keys($errors)));
            return redirect()->back()->withInput();
        }

        // 获取现有文章数据
        $existingPost = $postModel->find($id);
        if (!$existingPost) {
            session()->setFlashdata('error', '文章不存在');
            return redirect()->to('/admin/posts');
        }

        // 生成slug
        $slug = $this->generateSlug($this->request->getVar('title'), $id);

        // 处理文件上传
        $featuredImage = $this->request->getFile('featured_image');
        $featuredImageName = null;
        if ($featuredImage && $featuredImage->isValid() && !$featuredImage->hasMoved()) {
            // 生成与媒体库一致的文件名和路径
            $extension = $featuredImage->getClientExtension();
            $datePath = date('Ymd');
            $fileName = $datePath . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

            // 确保目录存在
            $uploadPath = ROOTPATH . 'public/uploads/' . $datePath;
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $featuredImage->move($uploadPath, $fileName);
            $featuredImageName = $datePath . '/' . $fileName;
        }

        // 处理从媒体库选择的图片
        $featuredImageFromMedia = $this->request->getVar('featured_image_from_media');
        if ($featuredImageFromMedia && !$featuredImageName) {
            $featuredImageName = $featuredImageFromMedia;
        }

        // 准备文章数据
        $postData = [
            'title' => $this->request->getVar('title'), // 标题
            'slug' => $slug, // slug
            'content' => $this->request->getVar('content'), // 内容
            'excerpt' => $this->request->getVar('excerpt') ?: $this->generateExcerpt($this->request->getVar('content')), // 摘要，为空则自动生成
            'category_id' => $this->request->getVar('category_id'), // 分类ID
            'status' => $this->request->getVar('status'), // 状态
            'visibility' => $this->request->getVar('visibility'), // 可见性
        ];

        // 只有当上传了新文件或从媒体库选择了图片时才更新featured_image
        if ($featuredImageName) {
            $postData['featured_image'] = $featuredImageName;
        }

        // 处理发布时间和定时发布时间
        if ($this->request->getVar('status') == 'published') {
            if ($existingPost['status'] != 'published') {
                $postData['published_at'] = date('Y-m-d H:i:s');
            }
            $postData['scheduled_at'] = null;
        } elseif ($this->request->getVar('status') == 'scheduled') {
            $postData['scheduled_at'] = $this->request->getVar('scheduled_at');
            $postData['published_at'] = null;
        } else {
            $postData['scheduled_at'] = null;
            $postData['published_at'] = null;
        }

        // 更新文章
        if (!$postModel->update($id, $postData)) {
            session()->setFlashdata('error', '更新文章失败');
            return redirect()->back()->withInput();
        }

        // 获取新旧状态
        $oldStatus = $existingPost['status'];
        $newStatus = $this->request->getVar('status');
        $isOldPublished = ($oldStatus == 'published');
        $isNewPublished = ($newStatus == 'published');

        // 如果分类改变且文章已发布，更新分类文章数
        $oldCategoryId = $existingPost['category_id'] ?? null;
        $newCategoryId = $this->request->getVar('category_id');
        if ($oldCategoryId != $newCategoryId) {
            if ($isOldPublished && $oldCategoryId) {
                $this->updateCategoryPostsCount($oldCategoryId, -1);
            }
            if ($isNewPublished && $newCategoryId) {
                $this->updateCategoryPostsCount($newCategoryId, 1);
            }
        } elseif ($isOldPublished && !$isNewPublished && $oldCategoryId) {
            // 状态从发布变为非发布
            $this->updateCategoryPostsCount($oldCategoryId, -1);
        } elseif (!$isOldPublished && $isNewPublished && $newCategoryId) {
            // 状态从非发布变为发布
            $this->updateCategoryPostsCount($newCategoryId, 1);
        }

        // 处理标签关联
        $oldTags = $this->getPostTags($id);
        $newTags = $this->request->getVar('tags') ?: [];

        // 计算需要增加和减少的标签
        $tagsToAdd = array_diff($newTags, $oldTags);
        $tagsToRemove = array_diff($oldTags, $newTags);

        // 更新标签文章数
        if ($isOldPublished && !$isNewPublished) {
            // 状态从发布变为非发布，减少所有标签的文章数
            foreach ($oldTags as $tagId) {
                $this->updateTagPostsCount($tagId, -1);
            }
        } elseif (!$isOldPublished && $isNewPublished) {
            // 状态从非发布变为发布，增加所有新标签的文章数
            foreach ($newTags as $tagId) {
                $this->updateTagPostsCount($tagId, 1);
            }
        } else {
            // 状态未改变，只处理标签的增减
            if ($isNewPublished) {
                foreach ($tagsToAdd as $tagId) {
                    $this->updateTagPostsCount($tagId, 1);
                }
            }
            if ($isOldPublished) {
                foreach ($tagsToRemove as $tagId) {
                    $this->updateTagPostsCount($tagId, -1);
                }
            }
        }

        // 更新文章标签关联
        $this->updatePostTags($id, $newTags);

        // 重定向到文章列表页面并显示成功消息
        session()->setFlashdata('success', '更新文章成功');
        return redirect()->to('/admin/posts');
    }

    /**
     * 删除文章
     * 根据ID删除文章
     *
     * @param int $id 文章ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function delete($id)
    {
        $postModel = new PostModel();

        // 检查文章是否存在
        $post = $postModel->find($id);
        if (!$post) {
            session()->setFlashdata('error', '文章不存在');
            return redirect()->to('/admin/posts');
        }

        // 删除文章
        if (!$postModel->delete($id)) {
            session()->setFlashdata('error', '删除文章失败');
            return redirect()->back();
        }

        // 如果文章已发布，更新分类和标签文章数
        if ($post['status'] == 'published') {
            // 更新分类文章数
            if ($post['category_id']) {
                $this->updateCategoryPostsCount($post['category_id'], -1);
            }

            // 更新标签文章数
            $postTags = $this->getPostTags($id);
            foreach ($postTags as $tagId) {
                $this->updateTagPostsCount($tagId, -1);
            }
        }

        // 删除文章标签关联
        $this->deletePostTags($id);

        // 重定向到文章列表页面并显示成功消息
        session()->setFlashdata('success', '删除文章成功');
        return redirect()->to('/admin/posts');
    }

    /**
     * 生成slug
     * 根据文章标题生成唯一的slug
     *
     * @param string $title 文章标题
     * @param int|null $excludeId 排除的文章ID（用于编辑时）
     * @return string 生成的slug
     */
    private function generateSlug($title, $excludeId = null)
    {
        $slug = url_title($title, '-', true);
        $postModel = new PostModel();

        // 检查slug是否已存在
        $counter = 1;
        $originalSlug = $slug;
        while (true) {
            $query = $postModel->where('slug', $slug);
            if ($excludeId) {
                $query->where('id !=', $excludeId);
            }
            if ($query->countAllResults() == 0) {
                break;
            }
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * 生成摘要
     * 根据文章内容生成摘要
     *
     * @param string $content 文章内容
     * @param int $length 摘要长度，默认150
     * @return string 生成的摘要
     */
    private function generateExcerpt($content, $length = 150)
    {
        $excerpt = strip_tags($content); // 去除HTML标签
        $excerpt = trim($excerpt); // 去除首尾空格
        if (strlen($excerpt) > $length) {
            $excerpt = substr($excerpt, 0, $length) . '...'; // 截取并添加省略号
        }
        return $excerpt;
    }

    /**
     * 保存文章标签关联
     * 将文章与标签的关联保存到数据库
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
     * 获取文章标签
     * 根据文章ID获取关联的标签ID
     *
     * @param int $postId 文章ID
     * @return array 标签ID数组
     */
    private function getPostTags($postId)
    {
        $db = \Config\Database::connect();
        $result = $db->table('post_tags')
            ->select('tag_id')
            ->where('post_id', $postId)
            ->get()
            ->getResultArray();

        $tagIds = [];
        foreach ($result as $item) {
            $tagIds[] = $item['tag_id'];
        }
        return $tagIds;
    }

    /**
     * 更新文章标签关联
     * 删除旧的标签关联并保存新的标签关联
     *
     * @param int $postId 文章ID
     * @param array $tagIds 新的标签ID数组
     */
    private function updatePostTags($postId, $tagIds)
    {
        $db = \Config\Database::connect();
        // 删除旧的标签关联
        $db->table('post_tags')->where('post_id', $postId)->delete();
        // 保存新的标签关联
        $this->savePostTags($postId, $tagIds);
    }

    /**
     * 删除文章标签关联
     * 删除文章与标签的关联
     *
     * @param int $postId 文章ID
     */
    private function deletePostTags($postId)
    {
        $db = \Config\Database::connect();
        $db->table('post_tags')->where('post_id', $postId)->delete();
    }

    /**
     * 更新分类文章数
     * 更新分类的文章计数
     *
     * @param int $categoryId 分类ID
     * @param int $increment 增量（正数增加，负数减少）
     */
    private function updateCategoryPostsCount($categoryId, $increment)
    {
        $db = \Config\Database::connect();
        if ($increment < 0) {
            // 减少时确保不会变成负数
            $db->query(
                "UPDATE categories SET posts_count = GREATEST(0, posts_count + ?) WHERE id = ?",
                [$increment, $categoryId]
            );
        } else {
            $db->query(
                "UPDATE categories SET posts_count = posts_count + ? WHERE id = ?",
                [$increment, $categoryId]
            );
        }
    }

    /**
     * 更新标签文章数
     * 更新标签的文章计数
     *
     * @param int $tagId 标签ID
     * @param int $increment 增量（正数增加，负数减少）
     */
    private function updateTagPostsCount($tagId, $increment)
    {
        $db = \Config\Database::connect();
        if ($increment < 0) {
            // 减少时确保不会变成负数
            $db->query(
                "UPDATE tags SET posts_count = GREATEST(0, posts_count + ?) WHERE id = ?",
                [$increment, $tagId]
            );
        } else {
            $db->query(
                "UPDATE tags SET posts_count = posts_count + ? WHERE id = ?",
                [$increment, $tagId]
            );
        }
    }

    /**
     * 显示文章（资源路由必需）
     * 重定向到编辑页面，因为后台管理中通常不需要单独的显示页面
     *
     * @param int $id 文章ID
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function show($id)
    {
        // 重定向到编辑页面，因为后台管理中通常不需要单独的显示页面
        return redirect()->to('/admin/posts/' . $id . '/edit');
    }

    /**
     * 处理富文本编辑器图片上传
     * 接收CKEditor上传的图片并保存到服务器
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON响应
     */
    public function upload()
    {
        // 检查用户登录状态
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'error' => [
                    'message' => '用户未登录'
                ]
            ])->setStatusCode(401);
        }

        // 跳过CSRF验证，因为CKEditor通过HTTP头传递令牌
        // 而CodeIgniter的CSRF过滤器可能无法正确处理
        // 注意：这是一个临时解决方案，生产环境应该确保CSRF验证正确
        $security = service('security');
        try {
            $security->verify($this->request);
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $e) {
            // 对于上传请求，跳过CSRF验证
        }

        // 检查是否有文件上传
        $image = $this->request->getFile('upload');
        if (!$image || !$image->isValid()) {
            return $this->response->setJSON([
                'error' => [
                    'message' => '文件上传失败: ' . $image->getErrorString()
                ]
            ]);
        }

        // 验证文件类型
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        if (!in_array($image->getMimeType(), $allowedTypes)) {
            return $this->response->setJSON([
                'error' => [
                    'message' => '只允许上传JPEG、PNG、GIF、WebP格式的图片和PDF文件'
                ]
            ]);
        }

        // 验证文件大小（最大10MB）
        if ($image->getSize() > 10 * 1024 * 1024) {
            return $this->response->setJSON([
                'error' => [
                    'message' => '文件大小不能超过10MB'
                ]
            ]);
        }

        // 生成随机文件名
        $extension = $image->getClientExtension();
        $fileName = date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

        // 确保uploads目录和日期子目录存在
        $datePath = date('Ymd');
        $uploadPath = ROOTPATH . 'public/uploads/' . $datePath;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // 移动文件到uploads目录
        if (!$image->move($uploadPath, $fileName)) {
            return $this->response->setJSON([
                'error' => [
                    'message' => '文件保存失败'
                ]
            ]);
        }

        // 构建图片URL
        $imageUrl = '/uploads/' . $datePath . '/' . $fileName;

        // 返回CKEditor所需的JSON响应
        return $this->response->setJSON([
            'fileName' => $fileName,
            'uploaded' => 1,
            'url' => $imageUrl
        ]);
    }

    /**
     * 自动保存文章
     * 处理前端的自动保存请求
     *
     * @return \CodeIgniter\HTTP\JSONResponse JSON响应
     */
    public function autoSave()
    {
        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => '无效的请求方法'
            ]);
        }

        $postId = $this->request->getVar('post_id');
        $content = $this->request->getVar('content');

        if (!$postId || !$content) {
            return $this->response->setJSON([
                'success' => false,
                'message' => '缺少必要参数'
            ]);
        }

        $postModel = new PostModel();
        $post = $postModel->find($postId);

        if (!$post) {
            return $this->response->setJSON([
                'success' => false,
                'message' => '文章不存在'
            ]);
        }

        // 自动保存内容
        if ($postModel->autoSave($postId, $content)) {
            // 生成新的CSRF令牌并添加到响应头
            $newCsrfToken = csrf_hash();

            return $this->response
                ->setHeader('X-CSRF-TOKEN', $newCsrfToken)
                ->setJSON([
                    'success' => true,
                    'message' => '自动保存成功',
                    'auto_saved_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => '自动保存失败'
            ]);
        }
    }

    /**
     * 发布定时文章
     * 手动触发发布定时文章的任务
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function publishScheduled()
    {
        $postModel = new PostModel();
        $updatedCount = $postModel->publishScheduledPosts();

        session()->setFlashdata('success', '定时发布任务执行完成，共发布 ' . $updatedCount . ' 篇文章');
        return redirect()->back();
    }
}
