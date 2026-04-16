<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run()
    {
        // 按顺序运行种子
        $this->call('UserSeeder');
        $this->call('CategorySeeder');
        $this->call('TagSeeder');
        $this->call('PostSeeder');
        $this->call('CommentSeeder');
    }
}
