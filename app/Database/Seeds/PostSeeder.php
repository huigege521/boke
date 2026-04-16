<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title' => '欢迎使用博客系统',
                'slug' => 'welcome',
                'content' => '<h2>欢迎使用博客系统</h2><p>这是一个基于 CodeIgniter 4.6.3 和 PHP 8.1 开发的完整博客平台。</p><p>系统功能包括：</p><ul><li>文章管理</li><li>分类管理</li><li>标签管理</li><li>评论系统</li><li>用户管理</li><li>搜索功能</li><li>归档功能</li><li>RSS订阅</li></ul><p>希望您能喜欢这个系统！</p>',
                'excerpt' => '这是一个基于 CodeIgniter 4.6.3 和 PHP 8.1 开发的完整博客平台。',
                'user_id' => 1,
                'category_id' => 1,
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'title' => 'CodeIgniter 4.6.3 新特性',
                'slug' => 'codeigniter-463-features',
                'content' => '<h2>CodeIgniter 4.6.3 新特性</h2><p>CodeIgniter 4.6.3 带来了许多新特性和改进。</p><h3>主要特性包括：</h3><ul><li>性能优化</li><li>安全性增强</li><li>新的辅助函数</li><li>Bug 修复</li></ul><p>详细信息请查看官方文档。</p>',
                'excerpt' => 'CodeIgniter 4.6.3 带来了许多新特性和改进。',
                'user_id' => 1,
                'category_id' => 4,
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'title' => 'PHP 8.1 新特性',
                'slug' => 'php-81-features',
                'content' => '<h2>PHP 8.1 新特性</h2><p>PHP 8.1 引入了许多令人兴奋的新特性。</p><h3>主要特性：</h3><ul><li>枚举类型</li><li>只读属性</li><li>纤维（Fibers）</li><li>交集类型</li><li>纯交集类型</li></ul><p>这些特性使 PHP 开发更加高效和安全。</p>',
                'excerpt' => 'PHP 8.1 引入了许多令人兴奋的新特性，如枚举类型、只读属性等。',
                'user_id' => 1,
                'category_id' => 4,
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'title' => '前端开发趋势',
                'slug' => 'frontend-trends',
                'content' => '<h2>前端开发趋势</h2><p>前端开发领域正在快速发展，以下是一些最新趋势：</p><ul><li>WebAssembly 的应用</li><li>微前端架构</li><li>低代码/无代码平台</li><li>AI 辅助开发</li></ul><p>作为前端开发者，保持学习新技术非常重要。</p>',
                'excerpt' => '前端开发领域正在快速发展，WebAssembly、微前端等技术成为新趋势。',
                'user_id' => 2,
                'category_id' => 5,
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'title' => '程序员的日常',
                'slug' => 'programmer-daily-life',
                'content' => '<h2>程序员的日常</h2><p>作为一名程序员，每天的工作内容包括：</p><ul><li>编写代码</li><li>调试问题</li><li>学习新技术</li><li>与团队协作</li></ul><p>虽然有时会遇到挑战，但解决问题后的成就感是无与伦比的。</p>',
                'excerpt' => '程序员的日常工作包括编写代码、调试问题、学习新技术等。',
                'user_id' => 2,
                'category_id' => 2,
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // 使用查询构建器插入数据
        $this->db->table('posts')->insertBatch($data);

        // 插入文章标签关联数据
        $postTagsData = [
            ['post_id' => 1, 'tag_id' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['post_id' => 1, 'tag_id' => 2, 'created_at' => date('Y-m-d H:i:s')],
            ['post_id' => 2, 'tag_id' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['post_id' => 2, 'tag_id' => 3, 'created_at' => date('Y-m-d H:i:s')],
            ['post_id' => 3, 'tag_id' => 3, 'created_at' => date('Y-m-d H:i:s')],
            ['post_id' => 4, 'tag_id' => 4, 'created_at' => date('Y-m-d H:i:s')],
            ['post_id' => 5, 'tag_id' => 5, 'created_at' => date('Y-m-d H:i:s')]
        ];

        $this->db->table('post_tags')->insertBatch($postTagsData);
    }
}
