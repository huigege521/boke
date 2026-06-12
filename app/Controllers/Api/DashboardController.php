<?php

namespace App\Controllers\Api;

use App\Models\PostModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use App\Models\UserModel;
use App\Models\CommentModel;
use App\Models\ContactModel;

/**
 * 仪表盘API控制器
 * 提供仪表盘统计数据和最近动态的API接口
 */
class DashboardController extends BaseApiController
{
    /**
     * 获取仪表盘数据
     * 包括统计数据、图表数据、最近动态等
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON响应
     */
    public function index()
    {
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();
        $tagModel = new TagModel();
        $userModel = new UserModel();
        $commentModel = new CommentModel();
        $contactModel = new ContactModel();

        $totalPosts = $postModel->countAll();
        $publishedPosts = $postModel->where('status', 'published')->countAllResults();
        $draftPosts = $postModel->where('status', 'draft')->countAllResults();

        $categories = $categoryModel->findAll();
        $categoryData = [];
        foreach ($categories as $category) {
            $categoryData[] = [
                'name' => $category['name'],
                'count' => $postModel->where('category_id', $category['id'])->countAllResults()
            ];
        }

        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $count = $postModel
                ->where('created_at >=', $date . '-01')
                ->where('created_at <', date('Y-m', strtotime("+1 month", strtotime($date . '-01'))) . '-01')
                ->countAllResults();
            $monthlyData[] = [
                'month' => date('Y年m月', strtotime($date)),
                'count' => $count
            ];
        }

        $recentPosts = $postModel->orderBy('created_at', 'desc')->limit(5)->findAll();
        $recentComments = $commentModel->orderBy('created_at', 'desc')->limit(5)->findAll();
        $recentContacts = $contactModel->orderBy('created_at', 'desc')->limit(5)->findAll();

        return $this->success([
            'username' => session()->get('username') ?: '管理员',
            'stats' => [
                'total_posts' => $totalPosts,
                'published_posts' => $publishedPosts,
                'draft_posts' => $draftPosts,
                'pending_posts' => $totalPosts - $publishedPosts - $draftPosts,
                'total_categories' => $categoryModel->countAll(),
                'total_tags' => $tagModel->countAll(),
                'total_users' => $userModel->countAll(),
                'total_comments' => $commentModel->countAll(),
                'pending_comments' => $commentModel->where('status', 'pending')->countAllResults(),
                'total_contacts' => $contactModel->countAll(),
                'pending_contacts' => $contactModel->where('status', 'pending')->countAllResults(),
                'processed_contacts' => $contactModel->where('status', 'processed')->countAllResults(),
            ],
            'category_data' => $categoryData,
            'monthly_data' => $monthlyData,
            'recent_posts' => $recentPosts,
            'recent_comments' => $recentComments,
            'recent_contacts' => $recentContacts,
        ], '获取成功');
    }
}
