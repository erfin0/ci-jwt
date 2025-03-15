<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRefreshTokensTable extends Migration
{
    public function up()
    {
        // Mendefinisikan field-field untuk tabel refresh_tokens.
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                // Menggunakan RawSql untuk menetapkan default CURRENT_TIMESTAMP.
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        // Menetapkan kolom id sebagai primary key.
        $this->forge->addKey('id', true);

        // Menambahkan foreign key dari user_id ke tabel users (field id) dengan action CASCADE ON DELETE.
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        // Membuat tabel refresh_tokens.
        $this->forge->createTable('refresh_tokens');
    }

    public function down()
    {
        // Menghapus tabel refresh_tokens jika migrasi dibalik.
        $this->forge->dropTable('refresh_tokens');
    }
}
