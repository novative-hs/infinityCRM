<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTestsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
             'fk_labid' => [
                'type'           => 'INT',
                'constraint'     => 11,
                
            ],
            'code' => [
                // e.g. '1207', '5050' — kept as VARCHAR since these are
                // treated as lab codes, not numbers you'd do math on.
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'test_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'rate' => [
                // Price — DECIMAL avoids floating-point rounding issues.
                'type'       => 'INT',
                'constraint' => '10',
            ],
            'sample' => [
                // Specimen type required, e.g. 'Blood', 'Urine', 'Stool'.
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'reporting_time' => [
                // Free text, e.g. 'Same Day After 2 Hour', '24 Hours',
                // matching the style seen in the booking UI rather than a
                // strict numeric duration.
                'type'       => 'VARCHAR',
                'constraint' => 100,
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
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('tests');
    }

    public function down()
    {
        $this->forge->dropTable('tests');
    }
}