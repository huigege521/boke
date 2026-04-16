<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToContactsTable extends Migration
{
    public function up()
    {
        $fields = [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'processed'],
                'default'    => 'pending',
                'comment'    => '处理状态：pending-未处理，processed-已处理'
            ]
        ];
        $this->forge->addColumn('contacts', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('contacts', 'status');
    }
}
