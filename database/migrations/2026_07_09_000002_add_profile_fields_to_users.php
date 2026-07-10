<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama_lengkap')->nullable()->after('name');
            $table->string('jenis_kelamin', 20)->nullable()->after('nama_lengkap');
            $table->string('kota_asal')->nullable()->after('jenis_kelamin');
            $table->string('nomor_hp', 20)->nullable()->after('kota_asal');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nama_lengkap', 'jenis_kelamin', 'kota_asal', 'nomor_hp']);
        });
    }
};
