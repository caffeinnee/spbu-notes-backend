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
        // 1. Refine users table
        Schema::table('users', function (Blueprint $table) {
            // Drop hp column if it exists
            if (Schema::hasColumn('users', 'hp')) {
                $table->dropColumn('hp');
            }
            
            // Add UNIQUE constraint to username if not already unique
            $table->string('username', 255)->unique()->change();
            
            // Just update email size without trying to re-create the unique index
            $table->string('email', 255)->change();
        });

        // 2. Refine notes table
        Schema::table('notes', function (Blueprint $table) {
            // Ensure pin_hash is VARCHAR(255) and nullable
            $table->string('pin_hash', 255)->nullable()->change();
            // Ensure is_pin_locked is boolean with default false
            $table->boolean('is_pin_locked')->default(false)->change();
        });

        // 3. Refine attachments table
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('jenis_file', 20)->change();
            $table->string('mime_type', 100)->nullable()->change();
            $table->string('path_file', 500)->change();
            $table->bigInteger('ukuran_file')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('jenis_file', 255)->change();
            $table->string('mime_type', 255)->nullable()->change();
            $table->string('path_file', 255)->change();
            $table->integer('ukuran_file')->nullable()->change();
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->string('pin_hash', 255)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('hp', 13)->nullable();
            $table->dropUnique(['username']);
        });
    }
};
