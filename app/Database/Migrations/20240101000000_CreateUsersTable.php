<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
                'comment' => '用户ID'
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
                'comment' => '用户名'
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
                'comment' => '邮箱地址'
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => '密码（哈希存储）'
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => '真实姓名'
            ],
            'avatar' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '头像URL'
            ],
            'bio' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '个人简介'
            ],
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'editor', 'user'],
                'default' => 'user',
                'comment' => '用户角色'
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
                'comment' => '用户状态'
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
            ],
            'last_login' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '最后登录时间'
            ]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('email', true);
        $this->forge->addKey('username', true);
        $this->forge->addKey('role');
        $this->forge->addKey('status');
        $this->forge->addKey('last_login');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
