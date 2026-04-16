<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\TagModel;

class TagSeeder extends Seeder
{
    public function run()
    {
        $tagModel = new TagModel();

        $tags = [
            [
                'name' => 'PHP',
                'slug' => 'php',
                'description' => 'PHP编程语言',
                'posts_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'JavaScript',
                'slug' => 'javascript',
                'description' => 'JavaScript编程语言',
                'posts_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'CodeIgniter',
                'slug' => 'codeigniter',
                'description' => 'CodeIgniter框架',
                'posts_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'MySQL',
                'slug' => 'mysql',
                'description' => 'MySQL数据库',
                'posts_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Bootstrap',
                'slug' => 'bootstrap',
                'description' => 'Bootstrap前端框架',
                'posts_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '生活感悟',
                'slug' => 'life-thoughts',
                'description' => '生活感悟',
                'posts_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '旅行日记',
                'slug' => 'travel-diary',
                'description' => '旅行日记',
                'posts_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($tags as $tag) {
            $tagModel->insert($tag);
        }
    }
}
