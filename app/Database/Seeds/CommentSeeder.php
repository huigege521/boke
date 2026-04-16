<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\CommentModel;

class CommentSeeder extends Seeder
{
    public function run()
    {
        $commentModel = new CommentModel();

        $comments = [
            [
                'post_id' => 1,
                'user_id' => 2,
                'parent_id' => null,
                'content' => '这篇文章写得非常好，对我学习 CodeIgniter 4 很有帮助！',
                'author_name' => null,
                'author_email' => null,
                'author_ip' => '127.0.0.1',
                'status' => 'approved',
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
            ],
            [
                'post_id' => 1,
                'user_id' => 3,
                'parent_id' => 1,
                'content' => '我也觉得这篇文章很有用，特别是关于路由的部分。',
                'author_name' => null,
                'author_email' => null,
                'author_ip' => '127.0.0.1',
                'status' => 'approved',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'post_id' => 2,
                'user_id' => 1,
                'parent_id' => null,
                'content' => 'JavaScript 的异步编程确实是一个重要的概念，这篇文章解释得很清楚。',
                'author_name' => null,
                'author_email' => null,
                'author_ip' => '127.0.0.1',
                'status' => 'approved',
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            ],
            [
                'post_id' => 3,
                'user_id' => null,
                'parent_id' => null,
                'content' => 'MySQL 性能优化是一个大话题，这篇文章提到的技巧都很实用。',
                'author_name' => '游客',
                'author_email' => 'guest@example.com',
                'author_ip' => '127.0.0.1',
                'status' => 'approved',
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
            ],
            [
                'post_id' => 4,
                'user_id' => 2,
                'parent_id' => null,
                'content' => '生活中的小确幸确实值得我们去关注和记录，这篇文章写得很有感触。',
                'author_name' => null,
                'author_email' => null,
                'author_ip' => '127.0.0.1',
                'status' => 'approved',
                'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
            ],
        ];

        foreach ($comments as $comment) {
            $commentModel->insert($comment);
        }
    }
}
