<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => '设置ID'
            ],
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
                'comment'    => '设置键名'
            ],
            'value' => [
                'type'       => 'TEXT',
                'comment'    => '设置值'
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => '设置标题'
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['text', 'textarea', 'editor'],
                'default'    => 'text',
                'comment'    => '设置类型'
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
        $this->forge->createTable('settings');

        // 插入初始数据
        $data = [
            [
                'key' => 'about_title',
                'value' => '关于我们',
                'title' => '关于页面标题',
                'type' => 'text'
            ],
            [
                'key' => 'about_description',
                'value' => '欢迎来到我们的博客系统！这是一个基于 CodeIgniter 4 框架开发的现代化博客平台，旨在为用户提供一个简单、高效、美观的内容发布和分享平台。',
                'title' => '关于页面描述',
                'type' => 'textarea'
            ],
            [
                'key' => 'about_features',
                'value' => '<ul class="list-group list-group-flush mb-4">
<li class="list-group-item"><i class="fas fa-check-circle text-success mr-2"></i>现代化的响应式设计，适配各种设备</li>
<li class="list-group-item"><i class="fas fa-check-circle text-success mr-2"></i>强大的文章管理功能，支持富文本编辑</li>
<li class="list-group-item"><i class="fas fa-check-circle text-success mr-2"></i>灵活的分类和标签系统，方便内容组织</li>
<li class="list-group-item"><i class="fas fa-check-circle text-success mr-2"></i>用户友好的评论系统，促进交流互动</li>
<li class="list-group-item"><i class="fas fa-check-circle text-success mr-2"></i>高效的搜索功能，快速找到所需内容</li>
</ul>',
                'title' => '关于页面特点',
                'type' => 'editor'
            ],
            [
                'key' => 'team_intro',
                'value' => '我们是一个充满激情和创造力的开发团队，致力于为用户提供最好的博客系统体验。',
                'title' => '团队介绍',
                'type' => 'textarea'
            ],
            [
                'key' => 'team_members',
                'value' => json_encode([
                    [
                        'name' => '张三',
                        'position' => '系统架构师',
                        'color' => 'bg-info'
                    ],
                    [
                        'name' => '李四',
                        'position' => '前端开发',
                        'color' => 'bg-success'
                    ],
                    [
                        'name' => '王五',
                        'position' => '后端开发',
                        'color' => 'bg-warning'
                    ]
                ]),
                'title' => '团队成员',
                'type' => 'textarea'
            ]
        ];

        $this->db->table('settings')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('settings');
    }
}
