<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTagsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
                'comment' => '标签ID'
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
                'comment' => '标签名称'
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
                'comment' => '标签别名（URL友好）'
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '标签描述'
            ],
            'posts_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '标签下文章数量'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '创建时间'
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '更新时间'
            ]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('slug', true);
        $this->forge->addKey('posts_count');
        $this->forge->createTable('tags');
    }

    public function down()
    {
        $this->forge->dropTable('tags');
    }
}
