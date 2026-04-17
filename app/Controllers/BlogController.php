<?php
namespace App\Controllers;

use App\Models\PostModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use App\Models\CommentModel;

/**
 * 博客控制器
 * 负责博客文章、分类、标签、搜索、归档等前台展示功能
 */
class BlogController extends BaseController
{
    /**
     * 首页 - 文章列表
     *
     * @return string 视图字符串
     */
    public function index()
    {
        // 获取当前页码
        $page = $this->request->getGet('page') ?? 1;
        $page = max(1, (int) $page);

        // 每页显示的文章数量
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // 缓存键，区分热门文章和普通文章
        $isPopular = $page == 1; // 首页第一页通常是热门文章
        $cacheKey = 'home_page_' . date('Ymd') . '_page_' . $page . ($isPopular ? '_popular' : '');
        $cache = \Config\Services::cache();

        // 尝试从缓存获取数据
        $cachedData = $cache->get($cacheKey);
        if ($cachedData) {
            return view('frontend/home', $cachedData);
        }

        // 初始化模型
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();

        // 获取文章列表
        $posts = $postModel->getPublishedPosts($perPage, $offset);

        // 获取文章总数
        $totalPosts = \Config\Database::connect()->table('posts')
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->countAllResults();

        // 初始化分页器
        $pager = \Config\Services::pager();
        $pager->setPath('/');

        // 准备视图数据
        $data = [
            'title' => '首页 - 博客系统',
            'posts' => $posts,
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
            'pager' => $pager,
            'totalPosts' => $totalPosts,
            'perPage' => $perPage,
            'currentPage' => $page,
        ];

        // 缓存数据，根据热度设置不同有效期
        // 热门文章缓存时间短（30分钟），普通页面缓存时间长（2小时）
        $cacheTTL = $isPopular ? 1800 : 7200;
        $cache->save($cacheKey, $data, $cacheTTL);

        // 渲染首页视图
        return view('frontend/home', $data);
    }

    /**
     * 文章详情页
     *
     * @param string $slug 文章别名
     * @return string 视图字符串
     */
    public function post($slug)
    {
        // 缓存键，使用slug的MD5值
        $cacheKey = 'post_' . md5($slug);
        $cache = \Config\Services::cache();

        // 尝试从缓存获取静态数据
        $staticData = $cache->get($cacheKey);

        // 获取实时数据
        $postModel = new PostModel();
        $post = $postModel->getPostBySlug($slug);

        if (!$post) {
            return redirect()->to('/')->with('error', '文章不存在');
        }

        // 增加浏览次数
        $postModel->incrementViews($post['id']);

        // 获取评论
        $commentModel = new CommentModel();
        $comments = $commentModel->getCommentsByPostId($post['id'], 'approved');

        // 获取相关文章（同分类）
        $relatedPosts = [];
        if ($post['category_id']) {
            $relatedPosts = $postModel->select('posts.*, categories.name as category_name, categories.slug as category_slug')
                ->join('categories', 'categories.id = posts.category_id', 'left')
                ->where('posts.category_id', $post['category_id'])
                ->where('posts.id !=', $post['id'])
                ->where('posts.status', 'published')
                ->where('posts.visibility', 'public')
                ->orderBy('posts.published_at', 'desc')
                ->limit(5)
                ->findAll();
        }

        // 准备视图数据
        $data = [
            'title' => $post['meta_title'] ?? $post['title'],
            'post' => $post,
            'comments' => $comments,
            'relatedPosts' => $relatedPosts,
            'metaDescription' => $post['meta_description'] ?? $post['excerpt'] ?? mb_substr(strip_tags($post['content']), 0, 200),
            'metaKeywords' => $post['meta_keywords'] ?? '',
        ];

        // 缓存静态数据（不包括评论等动态内容）
        $cache->save($cacheKey, $data, 3600);

        // 渲染文章详情视图
        return view('frontend/post', $data);
    }

    /**
     * 提交评论
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function submitComment()
    {
        // 检查是否登录
        if (!session()->get('logged_in')) {
            return redirect()->to('/home/login')->with('error', '请先登录');
        }

        // 验证输入
        $rules = [
            'post_id' => 'required|integer',
            'content' => 'required|min_length[5]|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator ? $this->validator->getErrors() : [];
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $commentModel = new CommentModel();

        // 准备评论数据
        $commentData = [
            'post_id' => $this->request->getPost('post_id'),
            'user_id' => session()->get('user_id'),
            'content' => $this->request->getPost('content'),
            'parent_id' => $this->request->getPost('parent_id') ?: null,
            'author_ip' => $this->request->getIPAddress(),
            'status' => 'pending', // 默认待审核
        ];

        // 插入评论
        if ($commentModel->insert($commentData)) {
            return redirect()->back()->with('success', '评论提交成功，等待审核');
        } else {
            return redirect()->back()->withInput()->with('error', '评论提交失败');
        }
    }

    /**
     * 分类归档页
     *
     * @param string $slug 分类别名
     * @return string 视图字符串
     */
    public function category($slug)
    {
        $categoryModel = new CategoryModel();
        $postModel = new PostModel();
        $tagModel = new TagModel();

        // 获取分类信息
        $category = $categoryModel->where('slug', $slug)->first();
        if (!$category) {
            return redirect()->to('/')->with('error', '分类不存在');
        }

        // 分页参数
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // 获取该分类下的文章
        $posts = $postModel->select('posts.*, users.username, users.name as author_name, categories.name as category_name, categories.slug as category_slug')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.category_id', $category['id'])
            ->where('posts.status', 'published')
            ->where('posts.visibility', 'public')
            ->orderBy('posts.published_at', 'desc')
            ->limit($perPage, $offset)
            ->findAll();

        // 准备视图数据
        $data = [
            'title' => $category['name'] . ' - 分类',
            'category' => $category,
            'posts' => $posts,
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
            'pager' => \Config\Services::pager(),
            'perPage' => $perPage,
            'currentPage' => $page,
            'totalPosts' => $postModel->where('category_id', $category['id'])
                ->where('status', 'published')
                ->where('visibility', 'public')
                ->countAllResults(),
        ];

        return view('frontend/category', $data);
    }

    /**
     * 标签归档页
     *
     * @param string $slug 标签别名
     * @return string 视图字符串
     */
    public function tag($slug)
    {
        $tagModel = new TagModel();
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();

        // 获取标签信息
        $tag = $tagModel->where('slug', $slug)->first();
        if (!$tag) {
            return redirect()->to('/')->with('error', '标签不存在');
        }

        // 分页参数
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // 获取该标签下的文章
        $posts = $postModel->select('posts.*, users.username, users.name as author_name, categories.name as category_name, categories.slug as category_slug')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->join('post_tags', 'post_tags.post_id = posts.id', 'inner')
            ->where('post_tags.tag_id', $tag['id'])
            ->where('posts.status', 'published')
            ->where('posts.visibility', 'public')
            ->orderBy('posts.published_at', 'desc')
            ->limit($perPage, $offset)
            ->findAll();

        // 准备视图数据
        $data = [
            'title' => $tag['name'] . ' - 标签',
            'tag' => $tag,
            'posts' => $posts,
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
            'pager' => \Config\Services::pager(),
            'perPage' => $perPage,
            'currentPage' => $page,
            'totalPosts' => $postModel->join('post_tags', 'post_tags.post_id = posts.id', 'inner')
                ->where('post_tags.tag_id', $tag['id'])
                ->where('status', 'published')
                ->where('visibility', 'public')
                ->countAllResults(),
        ];

        return view('frontend/tag', $data);
    }

    /**
     * 搜索功能
     *
     * @return string 视图字符串
     */
    public function search()
    {
        $keyword = $this->request->getPost('keyword') ?? $this->request->getGet('q');

        if (empty($keyword)) {
            return redirect()->to('/')->with('error', '请输入搜索关键词');
        }

        $postModel = new PostModel();
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();

        // 分页参数
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // 搜索文章
        $posts = $postModel->searchPosts($keyword, $perPage, $offset);
        $totalResults = $postModel->getSearchCount($keyword);

        // 准备视图数据
        $data = [
            'title' => '搜索: ' . esc($keyword),
            'keyword' => $keyword,
            'posts' => $posts,
            'totalResults' => $totalResults,
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
            'pager' => \Config\Services::pager(),
        ];

        return view('frontend/search', $data);
    }

    /**
     * 归档页
     *
     * @param int $year 年份
     * @param int $month 月份
     * @return string 视图字符串
     */
    public function archive($year, $month)
    {
        // 验证日期
        if (!checkdate((int) $month, 1, (int) $year)) {
            return redirect()->to('/')->with('error', '无效的日期');
        }

        $postModel = new PostModel();
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();

        // 分页参数
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // 获取指定年月的文章
        $posts = $postModel->select('posts.*, users.username, users.name as author_name, categories.name as category_name, categories.slug as category_slug')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('YEAR(posts.published_at)', $year)
            ->where('MONTH(posts.published_at)', $month)
            ->where('posts.status', 'published')
            ->where('posts.visibility', 'public')
            ->orderBy('posts.published_at', 'desc')
            ->limit($perPage, $offset)
            ->findAll();

        // 准备视图数据
        $data = [
            'title' => sprintf('%d年%d月 - 归档', $year, $month),
            'year' => $year,
            'month' => $month,
            'posts' => $posts,
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
            'pager' => \Config\Services::pager(),
        ];

        return view('frontend/archive', $data);
    }
}