<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLinksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
                'comment' => '链接ID'
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => '链接名称'
            ],
            'url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => '链接地址'
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '链接描述'
            ],
            'logo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '链接logo'
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '排序权重'
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
                'comment' => '链接状态'
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
        $this->forge->addKey('status');
        $this->forge->addKey('sort_order');
        $this->forge->addKey(['status', 'sort_order']);
        $this->forge->createTable('links');
    }

    public function down()
    {
        $this->forge->dropTable('links');
    }
}
