<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFranchisesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'lab_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'city_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'contact_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'discount' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'is_deleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('lab_id', 'labs', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('city_id', 'cities', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('franchises');
    }

    public function down()
    {
        $this->forge->dropTable('franchises');
    }
}