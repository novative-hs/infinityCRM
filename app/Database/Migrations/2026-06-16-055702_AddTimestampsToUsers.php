<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTimestampsToUsers extends Migration
{
    public function up()
{
    $this->forge->addColumn('users', [
        'updated_at' => [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'created_at',
        ],
    ]);
}

public function down()
{
    $this->forge->dropColumn('users', 'updated_at');
}
}
