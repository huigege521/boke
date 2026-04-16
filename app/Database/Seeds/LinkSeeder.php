<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\LinkModel;

class LinkSeeder extends Seeder
{
    public function run()
    {
        $linkModel = new LinkModel();

        $links = [
            [
                'name' => 'CodeIgniter 官网',
                'url' => 'https://codeigniter.com/',
                'description' => 'CodeIgniter 官方网站',
                'logo' => 'https://codeigniter.com/assets/images/logo.svg',
                'sort_order' => 1,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'PHP 官网',
                'url' => 'https://www.php.net/',
                'description' => 'PHP 官方网站',
                'logo' => 'https://www.php.net/images/logos/new-php-logo.svg',
                'sort_order' => 2,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'MySQL 官网',
                'url' => 'https://www.mysql.com/',
                'description' => 'MySQL 官方网站',
                'logo' => 'https://www.mysql.com/common/logos/mysql-logo.svg',
                'sort_order' => 3,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Bootstrap 官网',
                'url' => 'https://getbootstrap.com/',
                'description' => 'Bootstrap 官方网站',
                'logo' => 'https://getbootstrap.com/docs/5.3/assets/brand/bootstrap-logo.svg',
                'sort_order' => 4,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'GitHub',
                'url' => 'https://github.com/',
                'description' => 'GitHub 代码托管平台',
                'logo' => 'https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png',
                'sort_order' => 5,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($links as $link) {
            $linkModel->insert($link);
        }
    }
}
