<?php
namespace App\Controllers\Admin;

use App\Models\PostModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use App\Models\UserModel;
use App\Models\CommentModel;
use App\Models\ContactModel;
use CodeIgniter\Controller;

/**
 * 仪表盘控制器
 * 负责后台管理仪表盘的显示，包括各种统计数据和最近动态
 */
class DashboardController extends Controller
{
    /**
     * 显示仪表盘
     * 检查登录状态并获取各种统计数据，包括文章、分类、标签、用户和评论的数量
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string 重定向响应或视图字符串
     */
    public function index()
    {
        // 检查登录状态
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        // 获取统计数据
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();
        $userModel = new UserModel();
        $commentModel = new CommentModel();
        $contactModel = new ContactModel();

        // 获取分类及其文章数量
        $categories = $categoryModel->findAll();
        $categoryData = [];
        foreach ($categories as $category) {
            $categoryData[] = [
                'name' => $category['name'],
                'count' => $postModel->where('category_id', $category['id'])->countAllResults()
            ];
        }

        // 获取月度文章发布数据
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $count = $postModel->where('created_at >=', $date . '-01')->where('created_at <', date('Y-m', strtotime("+1 month", strtotime($date . '-01'))) . '-01')->countAllResults();
            $monthlyData[] = [
                'month' => date('Y年m月', strtotime($date)),
                'count' => $count
            ];
        }

        // 准备视图数据
        $data = [
            'title' => '后台管理 - 仪表盘',
            'total_posts' => $postModel->countAll(), // 总文章数
            'published_posts' => $postModel->where('status', 'published')->countAllResults(), // 已发布文章数
            'draft_posts' => $postModel->where('status', 'draft')->countAllResults(), // 草稿文章数
            'total_categories' => $categoryModel->countAll(), // 总分类数
            'total_tags' => $tagModel->countAll(), // 总标签数
            'total_users' => $userModel->countAll(), // 总用户数
            'total_comments' => $commentModel->countAll(), // 总评论数
            'pending_comments' => $commentModel->where('status', 'pending')->countAllResults(), // 待审核评论数
            'total_contacts' => $contactModel->countAll(), // 总联系消息数
            'pending_contacts' => $contactModel->where('status', 'pending')->countAllResults(), // 未处理联系消息数
            'processed_contacts' => $contactModel->where('status', 'processed')->countAllResults(), // 已处理联系消息数
            'recent_posts' => $postModel->orderBy('created_at', 'desc')->limit(5)->findAll(), // 最近5篇文章
            'recent_comments' => $commentModel->orderBy('created_at', 'desc')->limit(5)->findAll(), // 最近5条评论
            'recent_contacts' => $contactModel->orderBy('created_at', 'desc')->limit(5)->findAll(), // 最近5条联系消息
            'category_data' => $categoryData, // 分类文章数量数据
            'monthly_data' => $monthlyData, // 月度文章发布数据
        ];

        // 渲染仪表盘视图
        return view('admin/dashboard', $data);
    }
}
