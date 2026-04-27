<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Super Admin
        $admin = User::create([
            'name' => 'Super Admin',
            'nip' => '000000000000000000',
            'email' => 'admin@ptapapuabarat.go.id',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'pangkat' => '-',
            'jabatan' => 'Administrator Sistem',
            'satuan_kerja' => 'PTA Papua Barat',
            'is_active' => true,
        ]);

        // Atasan Langsung
        $atasan = User::create([
            'name' => 'Dr. H. Ahmad Fauzi, S.H., M.H.',
            'nip' => '197001011995031001',
            'email' => 'atasan@ptapapuabarat.go.id',
            'password' => Hash::make('password'),
            'role' => 'atasan',
            'pangkat' => 'Pembina Utama Muda / IV-c',
            'jabatan' => 'Ketua PTA Papua Barat',
            'satuan_kerja' => 'PTA Papua Barat',
            'is_active' => true,
        ]);

        // Pegawai 1
        User::create([
            'name' => 'Muhammad Rizki, S.H.',
            'nip' => '199001151015011001',
            'email' => 'rizki@ptapapuabarat.go.id',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
            'pangkat' => 'Penata Muda Tk. I / III-b',
            'jabatan' => 'Analis Perkara Peradilan',
            'satuan_kerja' => 'PTA Papua Barat',
            'atasan_id' => $atasan->id,
            'is_active' => true,
        ]);

        // Pegawai 2
        User::create([
            'name' => 'Siti Nurhaliza, S.Kom.',
            'nip' => '199205201020012002',
            'email' => 'siti@ptapapuabarat.go.id',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
            'pangkat' => 'Penata Muda / III-a',
            'jabatan' => 'Pranata Komputer',
            'satuan_kerja' => 'PTA Papua Barat',
            'atasan_id' => $atasan->id,
            'is_active' => true,
        ]);
    }
}
