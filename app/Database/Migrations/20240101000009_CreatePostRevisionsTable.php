<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 创建文章修订历史表
 */
class CreatePostRevisionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '修订版本ID'
            ],
            'post_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => '文章ID'
            ],
            'version' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => '版本号'
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'comment'    => '修订时的标题'
            ],
            'content' => [
                'type'       => 'TEXT',
                'comment'    => '修订时的内容'
            ],
            'excerpt' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => '修订时的摘要'
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '修订时的分类ID'
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'published', 'pending', 'scheduled'],
                'default'    => 'draft',
                'comment'    => '修订时的状态'
            ],
            'visibility' => [
                'type'       => 'ENUM',
                'constraint' => ['public', 'private'],
                'default'    => 'public',
                'comment'    => '修订时的可见性'
            ],
            'featured_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => '修订时的特色图片'
            ],
            'change_summary' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => '修改说明/变更摘要'
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => '修改人ID'
            ],
            'is_current' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '是否为当前版本（0=否，1=是）'
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => '创建时间'
            ]
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('post_id');
        $this->forge->addKey('version');
        $this->forge->addForeignKey('post_id', 'posts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('post_revisions');
    }

    public function down()
    {
        $this->forge->dropTable('post_revisions');
    }
}
