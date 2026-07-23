<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop posts table if it exists
        Schema::dropIfExists('posts');

        // 2. Refactor users table
        Schema::table('users', function (Blueprint $table) {
            // Rename name to username
            if (Schema::hasColumn('users', 'name') && !Schema::hasColumn('users', 'username')) {
                $table->renameColumn('name', 'username');
            }
            // Modify role column size/type
            if (Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('karyawan')->change();
            }
        });

        // Migrate existing user roles: 'KR' -> 'karyawan', 'AM' -> 'admin'
        DB::table('users')->where('role', 'KR')->update(['role' => 'karyawan']);
        DB::table('users')->where('role', 'AM')->update(['role' => 'admin']);

        // 3. Refactor notes table
        Schema::table('notes', function (Blueprint $table) {
            // Rename pin_code to pin_hash and change type to varchar(255)
            if (Schema::hasColumn('notes', 'pin_code') && !Schema::hasColumn('notes', 'pin_hash')) {
                $table->renameColumn('pin_code', 'pin_hash');
            }
        });

        Schema::table('notes', function (Blueprint $table) {
            if (Schema::hasColumn('notes', 'pin_hash')) {
                $table->string('pin_hash', 255)->nullable()->change();
            }
        });

        // Hash existing pin codes in the notes table
        $notesWithPin = DB::table('notes')->whereNotNull('pin_hash')->get();
        foreach ($notesWithPin as $note) {
            // Check if the pin is already hashed (bcrypt hashes are 60 chars and start with $)
            if (!str_starts_with($note->pin_hash, '$2y$') && strlen($note->pin_hash) <= 6) {
                DB::table('notes')
                    ->where('id', $note->id)
                    ->update(['pin_hash' => bcrypt($note->pin_hash)]);
            }
        }

        // 4. Create attachments table
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained('notes')->onDelete('cascade');
            $table->string('nama_file');
            $table->string('path_file');
            $table->string('jenis_file'); // 'foto' atau 'dokumen'
            $table->string('mime_type')->nullable();
            $table->integer('ukuran_file')->nullable();
            $table->timestamps();
        });

        // 5. Migrate old paths from notes to attachments table
        $notes = DB::table('notes')->get();
        foreach ($notes as $note) {
            // Migrate fotos
            if (Schema::hasColumn('notes', 'foto_paths') && !empty($note->foto_paths)) {
                $fotos = explode('|', $note->foto_paths);
                foreach ($fotos as $foto) {
                    if (!empty(trim($foto))) {
                        $cleanFoto = trim($foto);
                        $fileName = basename($cleanFoto);
                        DB::table('attachments')->insert([
                            'note_id' => $note->id,
                            'nama_file' => $fileName,
                            'path_file' => $cleanFoto,
                            'jenis_file' => 'foto',
                            'mime_type' => 'image/jpeg', // default fallback
                            'ukuran_file' => 0, // default placeholder
                            'created_at' => $note->created_at,
                            'updated_at' => $note->updated_at,
                        ]);
                    }
                }
            }

            // Migrate dokumen
            if (Schema::hasColumn('notes', 'dokumen_paths') && !empty($note->dokumen_paths)) {
                $docs = explode('|', $note->dokumen_paths);
                foreach ($docs as $doc) {
                    if (!empty(trim($doc))) {
                        $cleanDoc = trim($doc);
                        $fileName = basename($cleanDoc);
                        DB::table('attachments')->insert([
                            'note_id' => $note->id,
                            'nama_file' => $fileName,
                            'path_file' => $cleanDoc,
                            'jenis_file' => 'dokumen',
                            'mime_type' => 'application/pdf', // default fallback
                            'ukuran_file' => 0, // default placeholder
                            'created_at' => $note->created_at,
                            'updated_at' => $note->updated_at,
                        ]);
                    }
                }
            }
        }

        // 6. Finally, drop the old paths columns from notes table
        Schema::table('notes', function (Blueprint $table) {
            if (Schema::hasColumn('notes', 'foto_paths')) {
                $table->dropColumn('foto_paths');
            }
            if (Schema::hasColumn('notes', 'dokumen_paths')) {
                $table->dropColumn('dokumen_paths');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add columns to notes
        Schema::table('notes', function (Blueprint $table) {
            $table->text('foto_paths')->nullable();
            $table->text('dokumen_paths')->nullable();
        });

        // Drop attachments table
        Schema::dropIfExists('attachments');

        // Restore columns
        Schema::table('notes', function (Blueprint $table) {
            if (Schema::hasColumn('notes', 'pin_hash')) {
                $table->renameColumn('pin_hash', 'pin_code');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->renameColumn('username', 'name');
            }
        });
    }
};
