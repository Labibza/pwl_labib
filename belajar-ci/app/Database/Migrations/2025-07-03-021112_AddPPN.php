<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPPN extends Migration
{
    public function up()
    {
        $fields = [
            'ppn' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
            'biaya_admin' => [
                'type' => 'DOUBLE',
                'null' => true,
            ],
        ];
        $this->forge->addColumn('transaction', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transaction', ['ppn', 'biaya_admin']);
    }
}
