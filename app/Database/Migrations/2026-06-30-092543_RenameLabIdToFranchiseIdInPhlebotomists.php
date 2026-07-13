<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameLabIdToFranchiseIdInPhlebotomists extends Migration
{
    public function up()
    {
        // Column ka naam aur type badlo (lab_id -> franchise_id)
        $this->forge->modifyColumn('phlebotomists', [
            'lab_id' => [
                'name'       => 'franchise_id',
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
        ]);

        // Naya foreign key (franchise_id -> franchises.id) lagao
        $this->forge->addForeignKey('franchise_id', 'franchises', 'id', 'SET NULL', 'CASCADE', 'phlebotomists');
        $this->forge->processIndexes('phlebotomists');
    }

    public function down()
    {
        $this->forge->dropForeignKey('phlebotomists', 'phlebotomists_franchise_id_foreign');

        $this->forge->modifyColumn('phlebotomists', [
            'franchise_id' => [
                'name'       => 'lab_id',
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
        ]);

        $this->forge->processIndexes('phlebotomists');
    }
}