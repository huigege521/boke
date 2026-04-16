<?php
namespace App\Controllers;

use App\Models\PostModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use App\Models\UserModel;
use App\Models\CommentModel;
use App\Models\ContactModel;
use App\Models\SettingModel;

/**
 * 首页控制器
 * 负责网站的主要功能，包括首页、文章详情、评论、分类、标签、用户登录注册等
 */
class Home extends BaseController
{
    /**
     * 首页
     * 显示网站首页，包含最新文章、分类和标签
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

        // 缓存键，包含页码
        $cacheKey = 'home_page_' . date('Ymd') . '_page_' . $page;
        $cache = \Config\Services::cache();

        // 尝试从缓存获取
        if ($cache->get($cacheKey)) {
            $data = $cache->get($cacheKey);
            return view('frontend/home', $data);
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
            'posts' => $posts, // 获取已发布的文章
            'categories' => $categoryModel->getAllCategories(), // 获取所有分类
            'tags' => $tagModel->getAllTags(), // 获取所有标签
            'pager' => $pager,
            'totalPosts' => $totalPosts,
            'perPage' => $perPage,
            'currentPage' => $page,
        ];

        // 缓存数据，有效期1小时
        $cache->save($cacheKey, $data, 3600);

        // 渲染首页视图
        return view('frontend/home', $data);
    }

    /**
     * 文章详情
     * 根据文章slug显示文章详情，包括评论
     *
     * @param string $slug 文章别名
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
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
            return view('errors/html/error_404', ['message' => '文章不存在']);
        }

        // 增加浏览次数
        $postModel->incrementViews($post['id']);

        // 从数据库获取最新的浏览量、评论量
        $post['views'] = $postModel->find($post['id'])['views'];
        $post['comments_count'] = $postModel->find($post['id'])['comments_count'];

        // 从数据库获取最新的评论
        $commentModel = new CommentModel();
        $comments = $commentModel->getCommentsByPostId($post['id']);

        // 检查静态缓存数据是否存在
        if ($staticData) {
            // 合并静态缓存数据和实时数据
            $data = array_merge($staticData, [
                'post' => array_merge($staticData['post'], ['views' => $post['views'], 'comments_count' => $post['comments_count']]),
                'comments' => $comments,
            ]);
            return view('frontend/post', $data);
        }

        // 缓存不存在，构建数据
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();
        $data = [
            'title' => $post['title'] . ' - 博客系统',
            'post' => $post,
            'comments' => $comments,
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
        ];

        // 缓存数据，有效期1天
        $cache->save($cacheKey, $data, 86400);

        // 渲染文章详情视图
        return view('frontend/post', $data);
    }

    /**
     * 生成验证码
     * 生成验证码图片并返回
     *
     * @return \CodeIgniter\HTTP\Response 响应对象
     */
    public function captcha()
    {
        $captcha = new \App\Libraries\Captcha(session());
        $imageData = $captcha->generate();

        // 从base64字符串中提取图片数据
        $imageData = substr($imageData, strpos($imageData, ',') + 1);
        $imageData = base64_decode($imageData);

        return $this->response->setContentType('image/png')->setBody($imageData);
    }

    /**
     * 添加评论
     * 处理评论提交，包括验证、防重复提交和保存评论
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function addComment()
    {
        if (strtolower($this->request->getMethod()) == 'post') {
            // 验证表单数据
            $rules = [
                'content' => 'required|min_length[5]|max_length[1000]', // 评论内容必填，长度5-1000
                'post_id' => 'required|is_natural_no_zero', // 文章ID必填，且为正整数
                'captcha' => 'required', // 验证码必填
            ];

            // 如果用户未登录，需要验证姓名和邮箱
            if (!session()->get('logged_in')) {
                $rules['name'] = 'required|min_length[2]|max_length[50]'; // 姓名必填，长度2-50
                $rules['email'] = 'required|valid_email'; // 邮箱必填，且为有效邮箱
            }

            if (!$this->validate($rules)) {
                session()->setFlashdata('error', '评论内容过短或验证码错误，请检查输入');
                return redirect()->back()->withInput();
            }

            // 验证验证码
            $captcha = new \App\Libraries\Captcha(session());
            if (!$captcha->verify($this->request->getVar('captcha'))) {
                session()->setFlashdata('error', '验证码错误，请重试');
                return redirect()->back()->withInput();
            }

            // 防重复提交 - 验证token
            $commentToken = $this->request->getVar('comment_token');
            $sessionToken = session()->get('comment_token');

            if (!$commentToken || $commentToken === $sessionToken) {
                session()->setFlashdata('error', '请勿重复提交评论');
                return redirect()->back()->withInput();
            }

            // 防重复提交 - 检查提交时间间隔
            $lastCommentTime = session()->get('last_comment_time');
            if ($lastCommentTime && (time() - $lastCommentTime) < 10) { // 10秒内不允许重复提交
                session()->setFlashdata('error', '评论提交过于频繁，请稍后再试');
                return redirect()->back()->withInput();
            }

            // 准备评论数据
            $commentData = [
                'post_id' => $this->request->getVar('post_id'), // 文章ID
                'content' => $this->request->getVar('content'), // 评论内容
                'author_ip' => $this->request->getIPAddress(), // 评论者IP
                'created_at' => date('Y-m-d H:i:s'), // 创建时间
                'updated_at' => date('Y-m-d H:i:s'), // 更新时间
            ];

            // 保存token到会话，用于下次验证
            session()->set('comment_token', $commentToken);
            session()->set('last_comment_time', time());

            // 如果用户已登录，使用用户信息
            if (session()->get('logged_in')) {
                $commentData['user_id'] = session()->get('user_id');
                $commentData['status'] = 'approved'; // 登录用户的评论自动通过
            } else {
                // 否则使用表单提交的信息
                $commentData['user_id'] = null;
                $commentData['author_name'] = $this->request->getVar('name');
                $commentData['author_email'] = $this->request->getVar('email');
                $commentData['status'] = 'pending'; // 游客评论需要审核
            }

            // 保存评论
            $commentModel = new CommentModel();
            if ($commentModel->insert($commentData)) {
                // 更新文章评论数
                $postModel = new PostModel();
                $post = $postModel->find($commentData['post_id']);
                if ($post) {
                    // 重新计算评论数
                    $commentCount = $commentModel->where('post_id', $post['id'])
                        ->where('status', 'approved')
                        ->countAllResults();

                    $postModel->update($post['id'], [
                        'comments_count' => $commentCount
                    ]);

                    // 删除文章缓存
                    $cacheKey = 'post_' . md5((string)$post['slug']);
                    $cache = \Config\Services::cache();
                    $cache->delete($cacheKey);
                }

                // 设置成功消息
                if (session()->get('logged_in')) {
                    session()->setFlashdata('success', '评论提交成功');
                } else {
                    session()->setFlashdata('success', '评论提交成功，等待审核');
                }
            } else {
                session()->setFlashdata('error', '评论提交失败，请重试');
            }

            // 重定向回文章页面
            $postModel = new PostModel();
            $post = $postModel->find($commentData['post_id']);
            if ($post) {
                return redirect()->to('/post/' . $post['slug']);
            } else {
                return redirect()->to('/');
            }
        }

        // 如果不是POST请求，重定向回首页
        return redirect()->to('/');
    }

    /**
     * 分类页面
     * 根据分类slug显示该分类下的文章
     *
     * @param string $slug 分类别名
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function category($slug)
    {
        // 获取当前页码
        $page = $this->request->getGet('page') ?? 1;
        $page = max(1, (int) $page);

        // 每页显示的文章数量
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // 缓存键，使用slug的MD5值和页码
        $cacheKey = 'category_' . md5($slug) . '_page_' . $page;
        $cache = \Config\Services::cache();

        // 尝试从缓存获取
        if ($cache->get($cacheKey)) {
            $data = $cache->get($cacheKey);
            // 检查缓存数据是否包含必要的字段
            if (isset($data['posts']) && is_array($data['posts'])) {
                $hasCategorySlug = true;
                foreach ($data['posts'] as $post) {
                    if (!isset($post['category_slug'])) {
                        $hasCategorySlug = false;
                        break;
                    }
                }
                if ($hasCategorySlug) {
                    return view('frontend/category', $data);
                }
            }
            // 缓存数据不完整，删除缓存
            $cache->delete($cacheKey);
        }

        // 初始化模型
        $categoryModel = new CategoryModel();
        $postModel = new PostModel();
        $tagModel = new TagModel();
        $category = $categoryModel->getCategoryBySlug($slug);

        if (!$category) {
            return view('errors/html/error_404', ['message' => '分类不存在']);
        }

        // 获取文章列表
        $posts = $postModel->getPostsByCategory($category['id'], $perPage, $offset);

        // 获取文章总数
        $totalPosts = \Config\Database::connect()->table('posts')
            ->where('category_id', $category['id'])
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->countAllResults();

        // 初始化分页器
        $pager = \Config\Services::pager();
        $pager->setPath('/category/' . $slug);

        // 准备视图数据
        $data = [
            'title' => $category['name'] . ' - 分类页面',
            'category' => $category,
            'posts' => $posts, // 获取该分类下的文章
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
            'pager' => $pager,
            'totalPosts' => $totalPosts,
            'perPage' => $perPage,
            'currentPage' => $page,
        ];

        // 缓存数据，有效期12小时
        $cache->save($cacheKey, $data, 43200);

        // 渲染分类页面视图
        return view('frontend/category', $data);
    }

    /**
     * 标签页面
     * 根据标签slug显示该标签下的文章
     *
     * @param string $slug 标签别名
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function tag($slug)
    {
        // 获取当前页码
        $page = $this->request->getGet('page') ?? 1;
        $page = max(1, (int) $page);

        // 每页显示的文章数量
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // 缓存键，使用slug的MD5值和页码
        $cacheKey = 'tag_' . md5($slug) . '_page_' . $page;
        $cache = \Config\Services::cache();

        // 尝试从缓存获取
        if ($cache->get($cacheKey)) {
            $data = $cache->get($cacheKey);
            // 检查缓存数据是否包含必要的字段
            if (isset($data['posts']) && is_array($data['posts'])) {
                $hasCategorySlug = true;
                foreach ($data['posts'] as $post) {
                    if (!isset($post['category_slug'])) {
                        $hasCategorySlug = false;
                        break;
                    }
                }
                if ($hasCategorySlug) {
                    return view('frontend/tag', $data);
                }
            }
            // 缓存数据不完整，删除缓存
            $cache->delete($cacheKey);
        }

        // 初始化模型
        $tagModel = new TagModel();
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();
        $tag = $tagModel->getTagBySlug($slug);

        if (!$tag) {
            return view('errors/html/error_404', ['message' => '标签不存在']);
        }

        // 获取文章列表
        $posts = $postModel->getPostsByTag($tag['id'], $perPage, $offset);

        // 获取文章总数
        $totalPosts = \Config\Database::connect()->table('posts')
            ->join('post_tags', 'post_tags.post_id = posts.id', 'left')
            ->where('post_tags.tag_id', $tag['id'])
            ->where('posts.status', 'published')
            ->where('posts.visibility', 'public')
            ->countAllResults();

        // 初始化分页器
        $pager = \Config\Services::pager();
        $pager->setPath('/tag/' . $slug);

        // 准备视图数据
        $data = [
            'title' => $tag['name'] . ' - 标签页面',
            'tag' => $tag,
            'posts' => $posts, // 获取该标签下的文章
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
            'pager' => $pager,
            'totalPosts' => $totalPosts,
            'perPage' => $perPage,
            'currentPage' => $page,
        ];

        // 缓存数据，有效期12小时
        $cache->save($cacheKey, $data, 43200);

        // 渲染标签页面视图
        return view('frontend/tag', $data);
    }

    /**
     * 关于我们
     * 显示关于我们页面
     *
     * @return string 视图字符串
     */
    public function about()
    {
        // 初始化模型
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();
        $settingModel = new SettingModel();

        // 准备视图数据
        $data = [
            'title' => '关于我们 - 博客系统',
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
            'about_title' => $settingModel->getValue('about_title', '关于我们'),
            'about_description' => $settingModel->getValue('about_description', '欢迎来到我们的博客系统！'),
            'about_features' => $settingModel->getValue('about_features', ''),
            'team_intro' => $settingModel->getValue('team_intro', '我们是一个充满激情和创造力的开发团队。'),
            'team_members' => json_decode($settingModel->getValue('team_members', '[]'), true),
        ];

        // 渲染关于我们页面视图
        return view('frontend/about', $data);
    }

    /**
     * 联系我们
     * 处理联系表单提交和显示联系我们页面
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function contact()
    {
        // 处理POST请求
        if (strtolower($this->request->getMethod()) == 'post') {
            // 验证表单数据
            $rules = [
                'name' => 'required|min_length[2]|max_length[100]',
                'email' => 'required|valid_email|max_length[100]',
                'subject' => 'required|min_length[3]|max_length[200]',
                'message' => 'required|min_length[5]'
            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('error', '表单验证失败，请检查输入');
                return redirect()->back()->withInput();
            }

            // 准备联系数据
            $contactData = [
                'name' => $this->request->getVar('name'),
                'email' => $this->request->getVar('email'),
                'subject' => $this->request->getVar('subject'),
                'message' => $this->request->getVar('message'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // 保存联系数据
            $contactModel = new ContactModel();
            if ($contactModel->insert($contactData)) {
                session()->setFlashdata('success', '消息发送成功，我们会尽快回复您');
                return redirect()->to('/contact');
            } else {
                session()->setFlashdata('error', '消息发送失败，请重试');
                return redirect()->back()->withInput();
            }
        }

        // 初始化模型
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();

        // 准备视图数据
        $data = [
            'title' => '联系我们 - 博客系统',
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
        ];

        // 渲染联系我们页面视图
        return view('frontend/contact', $data);
    }

    /**
     * 用户注册
     * 处理用户注册请求
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function register()
    {
        // 如果已登录，跳转到首页
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        if (strtolower($this->request->getMethod()) == 'post') {
            $userModel = new UserModel();

            // 验证表单数据
            $rules = [
                'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]', // 用户名必填，长度3-50，且唯一
                'email' => 'required|valid_email|is_unique[users.email]', // 邮箱必填，且为有效邮箱，且唯一
                'password' => 'required|min_length[6]', // 密码必填，长度至少6位
                'confirm_password' => 'required|matches[password]', // 确认密码必填，且与密码匹配
                'name' => 'required', // 姓名必填
            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('error', '表单验证失败，请检查输入');
                return redirect()->back()->withInput();
            }

            // 哈希密码
            $passwordHash = password_hash($this->request->getVar('password'), PASSWORD_DEFAULT);

            // 创建用户
            $userData = [
                'username' => $this->request->getVar('username'), // 用户名
                'email' => $this->request->getVar('email'), // 邮箱
                'password' => $passwordHash, // 哈希后的密码
                'name' => $this->request->getVar('name'), // 姓名
                'role' => 'user', // 角色
                'status' => 'active', // 状态
                'created_at' => date('Y-m-d H:i:s'), // 创建时间
                'updated_at' => date('Y-m-d H:i:s'), // 更新时间
            ];

            if ($userModel->insert($userData)) {
                session()->setFlashdata('success', '注册成功，请登录');
                return redirect()->to('/home/login');
            } else {
                session()->setFlashdata('error', '注册失败，请重试');
                return redirect()->back()->withInput();
            }
        }

        // 初始化模型
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();

        // 准备视图数据
        $data = [
            'title' => '用户注册 - 博客系统',
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
        ];

        // 渲染注册页面视图
        return view('frontend/register', $data);
    }

    /**
     * 用户登录
     * 处理用户登录请求
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function login()
    {
        // 如果已登录，跳转到首页
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        if (strtolower($this->request->getMethod()) == 'post') {

            $userModel = new UserModel();
            $email = $this->request->getVar('email');
            $password = $this->request->getVar('password');

            // 获取用户信息
            $user = $userModel->getUserByEmail($email);

            if (!$user) {
                session()->setFlashdata('error', '邮箱或密码错误');
                return redirect()->back()->withInput();
            }

            if (!password_verify($password, $user['password'])) {
                session()->setFlashdata('error', '邮箱或密码错误');
                return redirect()->back()->withInput();
            }

            if ($user['status'] != 'active') {
                session()->setFlashdata('error', '账号已被禁用');
                return redirect()->back()->withInput();
            }

            // 设置登录会话
            $sessionData = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'logged_in' => true,
            ];

            session()->set($sessionData);

            // 更新最后登录时间
            $userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

            session()->setFlashdata('success', '登录成功');

            // 根据用户角色跳转到不同页面
            if ($user['role'] === 'admin') {
                return redirect()->to('/admin/dashboard');
            } else {
                return redirect()->to('/');
            }
        }

        // 初始化模型
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();

        // 准备视图数据
        $data = [
            'title' => '用户登录 - 博客系统',
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
        ];

        // 渲染登录页面视图
        return view('frontend/login', $data);
    }

    /**
     * 用户登出
     * 处理用户登出请求
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function logout()
    {
        // 销毁会话
        session()->destroy();
        session()->setFlashdata('success', '已成功登出');
        return redirect()->to('/');
    }

    /**
     * 忘记密码
     * 处理忘记密码请求，发送重置链接
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function forgotPassword()
    {
        if (strtolower($this->request->getMethod()) == 'post') {
            $email = $this->request->getVar('email');
            $userModel = new UserModel();
            $user = $userModel->getUserByEmail($email);

            if (!$user) {
                session()->setFlashdata('error', '邮箱不存在');
                return redirect()->back()->withInput();
            }

            // 生成重置令牌
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1小时过期

            // 保存令牌到用户表
            $userModel->update($user['id'], [
                'reset_token' => $token,
                'reset_token_expires' => $expiresAt
            ]);

            // 构建重置链接
            $resetLink = base_url('home/resetPassword?token=' . $token);

            // 这里应该发送邮件，现在只是模拟

            
            // 实际项目中应该使用 CodeIgniter 的 Email 类发送邮件
            session()->setFlashdata('success', '重置链接已发送到您的邮箱，请在1小时内点击链接重置密码');
            return redirect()->to('/home/forgotPassword');
        }

        return view('frontend/forgot_password', [
            'title' => '找回密码 - 博客系统'
        ]);
    }

    /**
     * 重置密码
     * 处理密码重置请求
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function resetPassword()
    {
        $token = $this->request->getGet('token') ?? $this->request->getPost('token');

        if (!$token) {
            session()->setFlashdata('error', '无效的重置链接');
            return redirect()->to('/home/forgotPassword');
        }

        if (strtolower($this->request->getMethod()) == 'post') {
            $password = $this->request->getVar('password');
            $confirmPassword = $this->request->getVar('confirm_password');

            if ($password != $confirmPassword) {
                session()->setFlashdata('error', '两次输入的密码不一致');
                return redirect()->back()->withInput();
            }

            $userModel = new UserModel();
            $user = $userModel->getUserByResetToken($token);

            if (!$user) {
                session()->setFlashdata('error', '无效的重置链接');
                return redirect()->to('/home/forgotPassword');
            }

            if (strtotime($user['reset_token_expires']) < time()) {
                session()->setFlashdata('error', '重置链接已过期');
                return redirect()->to('/home/forgotPassword');
            }

            // 哈希新密码
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // 更新密码并清除重置令牌
            $userModel->update($user['id'], [
                'password' => $passwordHash,
                'reset_token' => null,
                'reset_token_expires' => null
            ]);

            session()->setFlashdata('success', '密码重置成功，请登录');
            return redirect()->to('/home/login');
        }

        return view('frontend/reset_password', [
            'title' => '重置密码 - 博客系统',
            'token' => $token
        ]);
    }

    /**
     * 个人资料
     * 显示个人资料页面
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function profile()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/home/login');
        }

        $userModel = new UserModel();
        $user = $userModel->find(session()->get('user_id'));

        if (!$user) {
            session()->setFlashdata('error', '用户不存在');
            return redirect()->to('/');
        }

        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();

        return view('frontend/profile', [
            'title' => '个人资料 - 博客系统',
            'user' => $user,
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags()
        ]);
    }

    /**
     * 更新个人资料
     * 处理个人资料更新请求
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function updateProfile()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/home/login');
        }

        if (strtolower($this->request->getMethod()) == 'post') {
            $userModel = new UserModel();
            $userId = session()->get('user_id');
            $user = $userModel->find($userId);

            if (!$user) {
                session()->setFlashdata('error', '用户不存在');
                return redirect()->to('/');
            }

            // 验证表单数据
            $rules = [
                'username' => 'required|min_length[3]|max_length[50]',
                'email' => 'required|valid_email',
                'name' => 'required'
            ];

            if (!$this->validate($rules)) {
                session()->setFlashdata('error', '表单验证失败，请检查输入');
                return redirect()->back()->withInput();
            }

            // 检查用户名是否被其他用户使用
            $existingUser = $userModel->where('username', $this->request->getVar('username'))
                ->where('id !=', $userId)
                ->first();

            if ($existingUser) {
                session()->setFlashdata('error', '用户名已被使用');
                return redirect()->back()->withInput();
            }

            // 检查邮箱是否被其他用户使用
            $existingUser = $userModel->where('email', $this->request->getVar('email'))
                ->where('id !=', $userId)
                ->first();

            if ($existingUser) {
                session()->setFlashdata('error', '邮箱已被使用');
                return redirect()->back()->withInput();
            }

            // 更新用户资料
            $userData = [
                'username' => $this->request->getVar('username'),
                'email' => $this->request->getVar('email'),
                'name' => $this->request->getVar('name'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($userModel->update($userId, $userData)) {
                // 更新会话中的用户信息
                $sessionData = [
                    'username' => $userData['username'],
                    'email' => $userData['email']
                ];
                session()->set($sessionData);

                session()->setFlashdata('success', '个人资料更新成功');
            } else {
                session()->setFlashdata('error', '个人资料更新失败，请重试');
            }

            return redirect()->to('/home/profile');
        }

        return redirect()->to('/home/profile');
    }

    /**
     * 修改密码
     * 处理密码修改请求
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function changePassword()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/home/login');
        }

        if (strtolower($this->request->getMethod()) == 'post') {
            $currentPassword = $this->request->getVar('current_password');
            $newPassword = $this->request->getVar('new_password');
            $confirmNewPassword = $this->request->getVar('confirm_new_password');

            if ($newPassword != $confirmNewPassword) {
                session()->setFlashdata('error', '两次输入的新密码不一致');
                return redirect()->back()->withInput();
            }

            $userModel = new UserModel();
            $userId = session()->get('user_id');
            $user = $userModel->find($userId);

            if (!$user) {
                session()->setFlashdata('error', '用户不存在');
                return redirect()->to('/');
            }

            // 验证当前密码
            if (!password_verify((string)$currentPassword, (string)$user['password'])) {
                session()->setFlashdata('error', '当前密码错误');
                return redirect()->back()->withInput();
            }

            // 哈希新密码
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            // 更新密码
            if (
                $userModel->update($userId, [
                    'password' => $passwordHash,
                    'updated_at' => date('Y-m-d H:i:s')
                ])
            ) {
                session()->setFlashdata('success', '密码修改成功');
            } else {
                session()->setFlashdata('error', '密码修改失败，请重试');
            }

            return redirect()->to('/home/profile');
        }

        return redirect()->to('/home/profile');
    }

    /**
     * 搜索功能
     * 根据关键词搜索文章
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function search()
    {
        $keyword = $this->request->getVar('keyword');

        if (!$keyword) {
            return redirect()->to('/');
        }

        // 初始化模型
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();

        // 准备视图数据
        $data = [
            'title' => '搜索结果 - ' . $keyword,
            'keyword' => $keyword,
            'posts' => $postModel->searchPosts($keyword), // 搜索文章
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
        ];

        // 渲染搜索结果页面视图
        return view('frontend/search', $data);
    }

    /**
     * 归档功能
     * 根据年份和月份显示归档文章
     *
     * @param int $year 年份
     * @param int $month 月份
     * @return string 视图字符串
     */
    public function archive($year, $month)
    {
        // 初始化模型
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();

        // 准备视图数据
        $data = [
            'title' => $year . '年' . $month . '月 - 归档页面',
            'year' => $year,
            'month' => $month,
            'posts' => $postModel->getPostsByDate($year, $month), // 获取指定日期的文章
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
        ];

        // 渲染归档页面视图
        return view('frontend/archive', $data);
    }

    /**
     * RSS订阅
     * 生成RSS订阅文件
     *
     * @return \CodeIgniter\HTTP\Response 响应对象
     */
    public function rss()
    {
        // 初始化模型
        $postModel = new PostModel();
        $posts = $postModel->getPublishedPosts(20); // 获取最近20篇文章

        // 准备视图数据
        $data = [
            'posts' => $posts,
            'last_build_date' => date('r'), // 最后构建时间
        ];

        // 渲染RSS视图并设置内容类型
        return $this->response->setContentType('application/rss+xml')->setBody(view('frontend/rss', $data));
    }

    /**
     * 站点地图
     * 生成站点地图XML文件
     *
     * @return \CodeIgniter\HTTP\Response 响应对象
     */
    public function sitemap()
    {
        // 初始化模型
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();

        // 准备视图数据
        $data = [
            'posts' => $postModel->getPublishedPosts(1000), // 获取最近1000篇文章
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags(),
            'last_modified' => date('Y-m-d'), // 最后修改日期
        ];

        // 渲染站点地图视图并设置内容类型
        return $this->response->setContentType('application/xml')->setBody(view('frontend/sitemap', $data));
    }
}
