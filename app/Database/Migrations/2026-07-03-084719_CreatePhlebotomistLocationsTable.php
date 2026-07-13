<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePhlebotomistLocations extends Migration
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
            'booking_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => false,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
            ],
            'lat' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => false,
            ],
            'lng' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => false,
            ],
            'accuracy' => [
                'type'    => 'FLOAT',
                'null'    => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('booking_id');
        $this->forge->addUniqueKey('token');
        $this->forge->createTable('phlebotomist_locations', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('phlebotomist_locations', true);
    }
}