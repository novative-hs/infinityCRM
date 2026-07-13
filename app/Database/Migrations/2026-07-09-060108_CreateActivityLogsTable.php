<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'entity_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50, // 'lab' | 'franchise' | 'phlebotomist'
            ],
            'entity_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 50, // registered, updated, deleted, activated, deactivated, phlebotomist_added, phlebotomist_imported...
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'performed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addForeignKey('performed_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('activity_logs');
    }

    public function down()
    {
        $this->forge->dropTable('activity_logs');
    }
}