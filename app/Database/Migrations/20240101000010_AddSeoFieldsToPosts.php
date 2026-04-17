<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 为文章表添加SEO元数据字段
 */
class AddSeoFieldsToPosts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('posts', [
            'meta_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'comment'    => 'SEO标题（页面title标签）',
                'after'      => 'auto_saved_content'
            ],
            'meta_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'SEO描述（meta description）',
                'after'      => 'meta_title'
            ],
            'meta_keywords' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'SEO关键词（meta keywords）',
                'after'      => 'meta_description'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('posts', ['meta_title', 'meta_description', 'meta_keywords']);
    }
}
