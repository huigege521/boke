<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => '技术',
                'slug' => 'tech',
                'description' => '技术相关文章',
                'parent_id' => null,
                'order' => 0,
                'posts_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => '生活',
                'slug' => 'life',
                'description' => '生活随笔',
                'parent_id' => null,
                'order' => 1,
                'posts_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => '学习',
                'slug' => 'study',
                'description' => '学习笔记',
                'parent_id' => null,
                'order' => 2,
                'posts_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'PHP',
                'slug' => 'php',
                'description' => 'PHP编程语言',
                'parent_id' => 1,
                'order' => 0,
                'posts_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => '前端',
                'slug' => 'frontend',
                'description' => '前端开发',
                'parent_id' => 1,
                'order' => 1,
                'posts_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // 使用查询构建器插入数据
        $this->db->table('categories')->insertBatch($data);
    }
}
