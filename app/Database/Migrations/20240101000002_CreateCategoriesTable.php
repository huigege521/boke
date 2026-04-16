<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '分类ID'
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
                'comment'    => '分类名称'
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
                'comment'    => '分类别名（URL友好）'
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => '分类描述'
            ],
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '父分类ID（自关联）'
            ],
            'order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => '排序权重'
            ],
            'posts_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => '分类下文章数量'
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
        $this->forge->addForeignKey('parent_id', 'categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('categories');
    }

    public function down()
    {
        $this->forge->dropTable('categories');
    }
}
