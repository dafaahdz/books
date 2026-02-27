<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateFilesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'fileid' => [
                'type' => 'BIGINT',
                'auto_increment' => true
            ],
            'filename' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false
            ],
            'filerealname' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false
            ],
            'filedirectory' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP')
            ],
            'created_by' => [
                'type' => 'BIGINT',
                'null' => true
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
            'updated_by' => [
                'type' => 'BIGINT',
                'null' => true
            ],
            'isActive' => [
                'type' => 'BOOLEAN',
                'default' => true
            ]
        ]);

        $this->forge->addKey('fileid', true);
        $this->forge->createTable('files');
    }

    public function down()
    {
        $this->forge->dropTable('files');
    }
}
