<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * 测试数据填充器
 */
class TestSeeder extends Seeder
{
    public function run()
    {
        // 创建测试用户
        $this->db->table('users')->insert([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'name' => 'Test User',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // 创建测试分类
        $this->db->table('categories')->insert([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'A test category',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // 创建测试标签
        $this->db->table('tags')->insert([
            'name' => 'Test Tag',
            'slug' => 'test-tag',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
