<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePostsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '文章ID'
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'comment'    => '文章标题'
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'unique'     => true,
                'comment'    => '文章别名（URL友好）'
            ],
            'content' => [
                'type'       => 'TEXT',
                'comment'    => '文章内容'
            ],
            'excerpt' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => '文章摘要'
            ],
            'featured_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '特色图片URL'
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => '作者ID（关联users表）'
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '分类ID（关联categories表）'
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'published', 'pending'],
                'default'    => 'draft',
                'comment'    => '文章状态'
            ],
            'visibility' => [
                'type'       => 'ENUM',
                'constraint' => ['public', 'private'],
                'default'    => 'public',
                'comment'    => '文章可见性'
            ],
            'views' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => '浏览次数'
            ],
            'comments_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => '评论数量'
            ],
            'published_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => '发布时间'
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => '创建时间'
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => '更新时间'
            ]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('posts');
    }

    public function down()
    {
        $this->forge->dropTable('posts');
    }
}
