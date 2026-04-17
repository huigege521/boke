<?php
namespace App\Controllers;

use App\Models\ContactModel;
use App\Models\SettingModel;

/**
 * 页面控制器
 * 负责关于页面、联系表单等静态页面功能
 */
class PageController extends BaseController
{
    /**
     * 关于页面
     *
     * @return string 视图字符串
     */
    public function about()
    {
        // 初始化模型
        $categoryModel = new \App\Models\CategoryModel();
        $tagModel = new \App\Models\TagModel();

        $data = [
            'title' => '关于我们 - 博客系统',
            'about_title' => '关于我们',
            'about_description' => '这是一个基于CodeIgniter 4开发的现代化博客系统，提供丰富的功能和友好的用户体验。',
            'about_features' => '<ul>
                <li>响应式设计，支持移动设备</li>
                <li>SEO优化，提升搜索引擎排名</li>
                <li>多用户管理，支持权限控制</li>
                <li>文章分类和标签管理</li>
                <li>评论系统和用户互动</li>
                <li>缓存机制，提升性能</li>
            </ul>',
            'team_intro' => '我们的团队由经验丰富的开发者和设计师组成，致力于为用户提供最好的博客体验。',
            'team_members' => [
                ['name' => '张三', 'position' => '创始人 & 开发者', 'color' => 'bg-primary'],
                ['name' => '李四', 'position' => 'UI/UX设计师', 'color' => 'bg-success'],
                ['name' => '王五', 'position' => '后端工程师', 'color' => 'bg-warning']
            ],
            // 为侧边栏传递数据
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags()
        ];

        return view('frontend/about', $data);
    }

    /**
     * 联系页面
     *
     * @return string 视图字符串
     */
    public function contact()
    {
        // 初始化模型
        $categoryModel = new \App\Models\CategoryModel();
        $tagModel = new \App\Models\TagModel();

        $data = [
            'title' => '联系我们 - 博客系统',
            // 为侧边栏传递数据
            'categories' => $categoryModel->getAllCategories(),
            'tags' => $tagModel->getAllTags()
        ];

        return view('frontend/contact', $data);
    }

    /**
     * 提交联系表单
     *
     * @return \CodeIgniter\HTTP\RedirectResponse 重定向响应
     */
    public function submitContact()
    {
        // 验证输入
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email',
            'subject' => 'required|min_length[5]|max_length[200]',
            'message' => 'required|min_length[10]|max_length[2000]',
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator ? $this->validator->getErrors() : [];
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $contactModel = new ContactModel();

        // 准备联系数据
        $contactData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'subject' => $this->request->getPost('subject'),
            'message' => $this->request->getPost('message'),
            'ip_address' => $this->request->getIPAddress(),
            'status' => 'unread',
        ];

        // 插入联系记录
        if ($contactModel->insert($contactData)) {
            // TODO: 发送邮件通知管理员
            // $this->sendEmailNotification($contactData);

            return redirect()->back()->with('success', '消息发送成功，我们会尽快回复您');
        } else {
            return redirect()->back()->withInput()->with('error', '消息发送失败，请稍后重试');
        }
    }

    /**
     * RSS订阅
     *
     * @return \CodeIgniter\HTTP\ResponseInterface RSS XML响应
     */
    public function rss()
    {
        $postModel = new \App\Models\PostModel();

        // 获取最新的20篇文章
        $posts = $postModel->select('posts.*, users.username, categories.name as category_name')
            ->join('users', 'users.id = posts.user_id', 'left')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.status', 'published')
            ->where('posts.visibility', 'public')
            ->orderBy('posts.published_at', 'desc')
            ->limit(20)
            ->findAll();

        // 构建RSS XML
        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0">' . "\n";
        $rss .= '<channel>' . "\n";
        $rss .= '<title>' . esc(env('app.siteName', '博客系统')) . '</title>' . "\n";
        $rss .= '<link>' . esc(base_url()) . '</link>' . "\n";
        $rss .= '<description>' . esc(env('app.siteDescription', '欢迎来到我们的博客')) . '</description>' . "\n";
        $rss .= '<language>zh-CN</language>' . "\n";

        foreach ($posts as $post) {
            $rss .= '<item>' . "\n";
            $rss .= '<title>' . esc($post['title']) . '</title>' . "\n";
            $rss .= '<link>' . esc(base_url('post/' . $post['slug'])) . '</link>' . "\n";
            $rss .= '<description>' . esc($post['excerpt'] ?? mb_substr(strip_tags($post['content']), 0, 200)) . '</description>' . "\n";
            $rss .= '<pubDate>' . date('r', strtotime($post['published_at'])) . '</pubDate>' . "\n";
            $rss .= '<guid>' . esc(base_url('post/' . $post['slug'])) . '</guid>' . "\n";
            $rss .= '</item>' . "\n";
        }

        $rss .= '</channel>' . "\n";
        $rss .= '</rss>';

        return $this->response
            ->setContentType('application/rss+xml')
            ->setBody($rss);
    }

    /**
     * 站点地图
     *
     * @return \CodeIgniter\HTTP\ResponseInterface Sitemap XML响应
     */
    public function sitemap()
    {
        $postModel = new \App\Models\PostModel();
        $categoryModel = new \App\Models\CategoryModel();
        $tagModel = new \App\Models\TagModel();

        // 获取所有文章
        $posts = $postModel->select('slug, updated_at')
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->findAll();

        // 获取所有分类
        $categories = $categoryModel->findAll();

        // 获取所有标签
        $tags = $tagModel->findAll();

        // 构建Sitemap XML
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // 首页
        $sitemap .= '<url>' . "\n";
        $sitemap .= '<loc>' . esc(base_url()) . '</loc>' . "\n";
        $sitemap .= '<lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $sitemap .= '<changefreq>daily</changefreq>' . "\n";
        $sitemap .= '<priority>1.0</priority>' . "\n";
        $sitemap .= '</url>' . "\n";

        // 文章页面
        foreach ($posts as $post) {
            $sitemap .= '<url>' . "\n";
            $sitemap .= '<loc>' . esc(base_url('post/' . $post['slug'])) . '</loc>' . "\n";
            $sitemap .= '<lastmod>' . date('Y-m-d', strtotime($post['updated_at'])) . '</lastmod>' . "\n";
            $sitemap .= '<changefreq>monthly</changefreq>' . "\n";
            $sitemap .= '<priority>0.8</priority>' . "\n";
            $sitemap .= '</url>' . "\n";
        }

        // 分类页面
        foreach ($categories as $category) {
            $sitemap .= '<url>' . "\n";
            $sitemap .= '<loc>' . esc(base_url('category/' . $category['slug'])) . '</loc>' . "\n";
            $sitemap .= '<changefreq>weekly</changefreq>' . "\n";
            $sitemap .= '<priority>0.6</priority>' . "\n";
            $sitemap .= '</url>' . "\n";
        }

        // 标签页面
        foreach ($tags as $tag) {
            $sitemap .= '<url>' . "\n";
            $sitemap .= '<loc>' . esc(base_url('tag/' . $tag['slug'])) . '</loc>' . "\n";
            $sitemap .= '<changefreq>weekly</changefreq>' . "\n";
            $sitemap .= '<priority>0.5</priority>' . "\n";
            $sitemap .= '</url>' . "\n";
        }

        $sitemap .= '</urlset>';

        return $this->response
            ->setContentType('application/xml')
            ->setBody($sitemap);
    }
}