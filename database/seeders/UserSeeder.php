<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin TKJ',
            'email' => 'admin@tkj.sch.id',
            'username' => 'admin',
        ]);

        foreach ([10, 11, 12] as $tingkat) {
            $wali = User::factory()->guru()->create([
                'name' => "Wali Kelas TKJ {$tingkat}",
                'email' => "wali.tkj{$tingkat}@tkj.sch.id",
                'username' => "wali.tkj{$tingkat}",
            ]);

            $kelas = Kelas::create([
                'tingkat' => $tingkat,
                'nama_rombel' => "XII TKJ 1",
                'wali_kelas_id' => $wali->id,
            ]);
            $kelas->update(['nama_rombel' => match ($tingkat) {
                10 => 'X TKJ 1',
                11 => 'XI TKJ 1',
                12 => 'XII TKJ 1',
            }]);

            User::factory()
                ->siswa()
                ->count(5)
                ->create(['kelas_id' => $kelas->id]);
        }

        User::factory()->guru()->create([
            'name' => 'Guru Produktif TKJ',
            'email' => 'guru@tkj.sch.id',
            'username' => 'guru.produktif',
        ]);
    }
}
