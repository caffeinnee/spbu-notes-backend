<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Creates a default Admin/Manajer account.
     *
     * Credentials:
     *   Email    : admin@spbu.com
     *   Password : admin123
     *   Role     : AM (Admin/Manajer)
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@spbu.com'],
            [
                'name'          => 'Admin SPBU',
                'email'         => 'admin@spbu.com',
                'password'      => Hash::make('admin123'),
                'nama_lengkap'  => 'Admin SPBU',
                'jenis_kelamin' => 'Laki-laki',
                'kota_asal'     => '-',
                'nomor_hp'      => '-',
                'role'          => 'AM',
            ]
        );

        $this->command->info('Admin account created: admin@spbu.com / admin123');
    }
}
