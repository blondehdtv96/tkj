<?php

namespace Tests\Feature;

use App\Events\PointsEarned;
use App\Events\RedemptionApproved;
use App\Models\Bab;
use App\Models\Kuis;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\OpsiJawaban;
use App\Models\PointsLog;
use App\Models\Redemption;
use App\Models\Reward;
use App\Models\Soal;
use App\Models\SoalPemahaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GamifikasiTest extends TestCase
{
    use RefreshDatabase;

    private function buatMateri(int $jumlahMateri = 1): Materi
    {
        $mapel = MataPelajaran::create(['nama' => 'TKJ', 'tingkat' => 10]);
        $bab = Bab::create(['mata_pelajaran_id' => $mapel->id, 'judul' => 'Bab 1', 'urutan' => 1]);

        $pertama = null;

        foreach (range(1, $jumlahMateri) as $i) {
            $materi = Materi::create([
                'bab_id' => $bab->id,
                'judul' => "Materi {$i}",
                'konten_html' => '<p>Isi</p>',
                'tipe' => 'teks',
                'urutan' => $i,
            ]);

            $pertama ??= $materi;
        }

        return $pertama;
    }

    private function buatKuis(Bab $bab): Kuis
    {
        $kuis = Kuis::create([
            'bab_id' => $bab->id,
            'judul' => 'Kuis 1',
            'durasi_menit' => 20,
            'kkm' => 75,
            'acak_soal' => false,
            'aktif' => true,
        ]);

        foreach (range(1, 2) as $i) {
            $soal = Soal::create([
                'kuis_id' => $kuis->id,
                'pertanyaan' => "Soal {$i}",
                'tipe' => 'pilihan_ganda',
                'bobot' => 10,
                'poin' => 25,
            ]);
            OpsiJawaban::create(['soal_id' => $soal->id, 'teks' => 'Benar', 'is_benar' => true]);
            OpsiJawaban::create(['soal_id' => $soal->id, 'teks' => 'Salah', 'is_benar' => false]);
        }

        return $kuis;
    }

    /**
     * Soal troubleshooting: langkah disimpan sesuai urutan yang benar.
     *
     * @return array{0: Soal, 1: \Illuminate\Support\Collection}
     */
    private function buatSoalTroubleshooting(Kuis $kuis, int $poin = 40): array
    {
        $soal = Soal::create([
            'kuis_id' => $kuis->id,
            'pertanyaan' => 'Susun langkah penanganan yang benar.',
            'skenario' => 'PC baru dirakit tidak menyala sama sekali.',
            'tipe' => 'troubleshooting',
            'bobot' => 20,
            'poin' => $poin,
        ]);

        $langkah = collect([
            'Periksa kabel power ke stop kontak',
            'Periksa konektor 24-pin ke motherboard',
            'Tes power supply dengan paperclip',
            'Cek RAM terpasang dengan benar',
        ])->map(fn ($teks, $i) => OpsiJawaban::create([
            'soal_id' => $soal->id,
            'teks' => $teks,
            'is_benar' => true,
            'urutan' => $i + 1,
        ]));

        return [$soal, $langkah];
    }

    public function test_menyelesaikan_materi_memberi_poin_dan_bonus_bab(): void
    {
        $materi = $this->buatMateri();
        $siswa = User::factory()->siswa()->create();

        $response = $this->actingAs($siswa)->postJson("/api/materi/{$materi->id}/selesai");

        $response->assertOk();

        // 10 poin materi + 50 bonus bab tuntas (bab hanya berisi satu materi).
        $this->assertSame(60, $response->json('poin_diperoleh'));
        $this->assertSame(60, (int) $siswa->fresh()->total_poin);
        $this->assertDatabaseHas('points_log', ['user_id' => $siswa->id, 'sumber' => 'materi', 'jumlah' => 10]);
        $this->assertDatabaseHas('points_log', ['user_id' => $siswa->id, 'sumber' => 'bonus_bab', 'jumlah' => 50]);
    }

    public function test_menandai_materi_selesai_dua_kali_tidak_menggandakan_poin(): void
    {
        $materi = $this->buatMateri();
        $siswa = User::factory()->siswa()->create();

        $this->actingAs($siswa)->postJson("/api/materi/{$materi->id}/selesai")->assertOk();
        $this->actingAs($siswa)->postJson("/api/materi/{$materi->id}/selesai")->assertOk();

        $this->assertSame(60, (int) $siswa->fresh()->total_poin);
        $this->assertSame(2, PointsLog::where('user_id', $siswa->id)->count());
    }

    public function test_materi_bersoal_pemahaman_menolak_klik_tanpa_jawaban_benar(): void
    {
        $materi = $this->buatMateri();
        $soal = SoalPemahaman::create([
            'materi_id' => $materi->id,
            'pertanyaan' => 'Apa kepanjangan dari TKJ?',
            'opsi' => ['Teknik Komputer dan Jaringan', 'Teknik Kelistrikan Jalan'],
            'jawaban_benar' => 0,
        ]);
        $siswa = User::factory()->siswa()->create();

        // Tanpa payload jawaban sama sekali.
        $this->actingAs($siswa)
            ->postJson("/api/materi/{$materi->id}/selesai")
            ->assertStatus(422);

        // Jawaban salah.
        $this->actingAs($siswa)
            ->postJson("/api/materi/{$materi->id}/selesai", ['jawaban' => [['soal_id' => $soal->id, 'jawaban' => 1]]])
            ->assertStatus(422);

        $this->assertSame(0, (int) $siswa->fresh()->total_poin);
        $this->assertDatabaseMissing('progres_materi', ['user_id' => $siswa->id, 'status' => 'selesai']);

        // Jawaban benar.
        $this->actingAs($siswa)
            ->postJson("/api/materi/{$materi->id}/selesai", ['jawaban' => [['soal_id' => $soal->id, 'jawaban' => 0]]])
            ->assertOk();

        $this->assertSame(60, (int) $siswa->fresh()->total_poin);
    }

    public function test_siswa_tidak_menerima_kunci_soal_pemahaman(): void
    {
        $materi = $this->buatMateri();
        SoalPemahaman::create([
            'materi_id' => $materi->id,
            'pertanyaan' => 'Apa kepanjangan dari TKJ?',
            'opsi' => ['Teknik Komputer dan Jaringan', 'Teknik Kelistrikan Jalan'],
            'jawaban_benar' => 0,
        ]);
        $siswa = User::factory()->siswa()->create();

        $response = $this->actingAs($siswa)->getJson("/api/soal-pemahaman?materi_id={$materi->id}");

        $response->assertOk();
        $response->assertJsonMissingPath('data.0.jawaban_benar');

        $guru = User::factory()->guru()->create();
        $this->actingAs($guru)
            ->getJson("/api/soal-pemahaman?materi_id={$materi->id}")
            ->assertJsonPath('data.0.jawaban_benar', 0);
    }

    public function test_membuka_materi_tidak_membuka_kunci_kuis(): void
    {
        $materi = $this->buatMateri();
        $kuis = $this->buatKuis($materi->bab);
        $siswa = User::factory()->siswa()->create();

        // Sekadar membuka materi hanya tercatat sebagai "dibaca".
        $this->actingAs($siswa)->getJson("/api/materi/{$materi->id}")->assertOk();

        $this->assertDatabaseHas('progres_materi', [
            'user_id' => $siswa->id,
            'materi_id' => $materi->id,
            'status' => 'dibaca',
        ]);

        $this->actingAs($siswa)
            ->postJson("/api/kuis/{$kuis->id}/mulai")
            ->assertStatus(422);
    }

    public function test_submit_kuis_memberi_poin_skor_bonus_kkm_dan_tepat_waktu(): void
    {
        Event::fake([PointsEarned::class]);

        $materi = $this->buatMateri();
        $kuis = $this->buatKuis($materi->bab);
        $siswa = User::factory()->siswa()->create();

        $this->actingAs($siswa)->postJson("/api/materi/{$materi->id}/selesai")->assertOk();
        $poinSetelahMateri = (int) $siswa->fresh()->total_poin;

        $mulai = $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai");
        $hasilKuisId = $mulai->json('data.hasil_kuis_id');

        $jawaban = collect($mulai->json('data.soal'))->map(fn ($soal) => [
            'soal_id' => $soal['id'],
            'opsi_id' => $soal['opsi_jawaban'][0]['id'],
        ])->all();

        $response = $this->actingAs($siswa)->postJson("/api/hasil-kuis/{$hasilKuisId}/submit", ['jawaban' => $jawaban]);

        $response->assertOk();
        $response->assertJsonPath('data.skor', 100);

        // 2 soal benar x 25 poin + 20 bonus KKM + 15 bonus tepat waktu.
        $this->assertSame(85, $response->json('gamifikasi.poin_diperoleh'));
        $this->assertSame($poinSetelahMateri + 85, (int) $siswa->fresh()->total_poin);

        Event::assertDispatched(PointsEarned::class);
    }

    public function test_level_naik_mengikuti_total_poin(): void
    {
        $siswa = User::factory()->siswa()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/gamifikasi/poin/penyesuaian', [
            'user_id' => $siswa->id,
            'jumlah' => 1600,
            'keterangan' => 'Juara lomba jaringan',
        ])->assertStatus(201);

        $siswa->refresh();
        $this->assertSame(1600, (int) $siswa->total_poin);
        $this->assertSame('Network Specialist', $siswa->level);

        $this->actingAs($siswa)
            ->getJson('/api/gamifikasi/ringkasan')
            ->assertOk()
            ->assertJsonPath('data.level.level', 'Network Specialist')
            ->assertJsonPath('data.level.level_berikutnya', 'Master Technician');
    }

    public function test_siswa_tidak_bisa_melakukan_penyesuaian_poin(): void
    {
        $siswa = User::factory()->siswa()->create();

        $this->actingAs($siswa)->postJson('/api/gamifikasi/poin/penyesuaian', [
            'user_id' => $siswa->id,
            'jumlah' => 5000,
            'keterangan' => 'Poin gratis',
        ])->assertStatus(403);

        $this->assertSame(0, (int) $siswa->fresh()->total_poin);
    }

    public function test_redeem_menahan_poin_dan_mengurangi_stok(): void
    {
        $siswa = User::factory()->siswa()->create(['total_poin' => 500, 'level' => 'Teknisi Junior']);
        $reward = Reward::create(['nama_barang' => 'Pulpen', 'harga_poin' => 150, 'stok' => 2]);

        $response = $this->actingAs($siswa)->postJson('/api/gamifikasi/redemptions', ['reward_id' => $reward->id]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.status', 'pending');

        $this->assertSame(350, (int) $siswa->fresh()->total_poin);
        $this->assertSame(1, (int) $reward->fresh()->stok);
        $this->assertDatabaseHas('points_log', ['user_id' => $siswa->id, 'sumber' => 'redeem', 'jumlah' => -150]);
    }

    public function test_redeem_ditolak_bila_poin_tidak_cukup(): void
    {
        $siswa = User::factory()->siswa()->create(['total_poin' => 100]);
        $reward = Reward::create(['nama_barang' => 'Tang Crimping', 'harga_poin' => 900, 'stok' => 5]);

        $this->actingAs($siswa)
            ->postJson('/api/gamifikasi/redemptions', ['reward_id' => $reward->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors('reward_id');

        $this->assertSame(100, (int) $siswa->fresh()->total_poin);
        $this->assertSame(5, (int) $reward->fresh()->stok);
        $this->assertDatabaseCount('redemptions', 0);
    }

    public function test_redeem_ditolak_bila_stok_habis(): void
    {
        $siswa = User::factory()->siswa()->create(['total_poin' => 1000]);
        $reward = Reward::create(['nama_barang' => 'Kaos', 'harga_poin' => 200, 'stok' => 0]);

        $this->actingAs($siswa)
            ->postJson('/api/gamifikasi/redemptions', ['reward_id' => $reward->id])
            ->assertStatus(422);

        $this->assertSame(1000, (int) $siswa->fresh()->total_poin);
    }

    public function test_guru_menyetujui_penukaran(): void
    {
        Event::fake([RedemptionApproved::class]);

        $siswa = User::factory()->siswa()->create(['total_poin' => 500]);
        $guru = User::factory()->guru()->create();
        $reward = Reward::create(['nama_barang' => 'Pulpen', 'harga_poin' => 150, 'stok' => 2]);

        $redemption = Redemption::create([
            'user_id' => $siswa->id,
            'reward_id' => $reward->id,
            'jumlah_poin' => 150,
            'status' => 'pending',
        ]);

        $this->actingAs($guru)
            ->postJson("/api/gamifikasi/redemptions/{$redemption->id}/status", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.petugas.id', $guru->id);

        Event::assertDispatched(RedemptionApproved::class);
    }

    public function test_penolakan_mengembalikan_poin_dan_stok(): void
    {
        $siswa = User::factory()->siswa()->create(['total_poin' => 500]);
        $guru = User::factory()->guru()->create();
        $reward = Reward::create(['nama_barang' => 'Pulpen', 'harga_poin' => 150, 'stok' => 5]);

        $this->actingAs($siswa)->postJson('/api/gamifikasi/redemptions', ['reward_id' => $reward->id])->assertStatus(201);
        $redemption = Redemption::firstOrFail();

        $this->assertSame(350, (int) $siswa->fresh()->total_poin);
        $this->assertSame(4, (int) $reward->fresh()->stok);

        $this->actingAs($guru)
            ->postJson("/api/gamifikasi/redemptions/{$redemption->id}/status", [
                'status' => 'rejected',
                'catatan_petugas' => 'Stok fisik sedang kosong.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->assertSame(500, (int) $siswa->fresh()->total_poin);
        $this->assertSame(5, (int) $reward->fresh()->stok);
        $this->assertDatabaseHas('points_log', ['user_id' => $siswa->id, 'sumber' => 'refund', 'jumlah' => 150]);
    }

    public function test_penolakan_wajib_menyertakan_alasan(): void
    {
        $siswa = User::factory()->siswa()->create(['total_poin' => 500]);
        $guru = User::factory()->guru()->create();
        $reward = Reward::create(['nama_barang' => 'Pulpen', 'harga_poin' => 150, 'stok' => 5]);

        $this->actingAs($siswa)->postJson('/api/gamifikasi/redemptions', ['reward_id' => $reward->id]);
        $redemption = Redemption::firstOrFail();

        $this->actingAs($guru)
            ->postJson("/api/gamifikasi/redemptions/{$redemption->id}/status", ['status' => 'rejected'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('catatan_petugas');
    }

    public function test_penukaran_yang_sudah_diproses_tidak_dapat_diproses_ulang(): void
    {
        $siswa = User::factory()->siswa()->create(['total_poin' => 500]);
        $guru = User::factory()->guru()->create();
        $reward = Reward::create(['nama_barang' => 'Pulpen', 'harga_poin' => 150, 'stok' => 5]);

        $this->actingAs($siswa)->postJson('/api/gamifikasi/redemptions', ['reward_id' => $reward->id]);
        $redemption = Redemption::firstOrFail();

        $this->actingAs($guru)->postJson("/api/gamifikasi/redemptions/{$redemption->id}/status", [
            'status' => 'rejected',
            'catatan_petugas' => 'Stok kosong.',
        ])->assertOk();

        $this->actingAs($guru)->postJson("/api/gamifikasi/redemptions/{$redemption->id}/status", [
            'status' => 'approved',
        ])->assertStatus(422);

        // Poin hanya dikembalikan satu kali.
        $this->assertSame(500, (int) $siswa->fresh()->total_poin);
    }

    public function test_siswa_hanya_melihat_riwayat_poin_dan_penukaran_miliknya(): void
    {
        $siswa = User::factory()->siswa()->create();
        $siswaLain = User::factory()->siswa()->create();
        $reward = Reward::create(['nama_barang' => 'Pulpen', 'harga_poin' => 10, 'stok' => 5]);

        PointsLog::create(['user_id' => $siswaLain->id, 'sumber' => 'materi', 'jumlah' => 10, 'keterangan' => 'Materi lain']);
        Redemption::create(['user_id' => $siswaLain->id, 'reward_id' => $reward->id, 'jumlah_poin' => 10, 'status' => 'pending']);

        $this->actingAs($siswa)->getJson('/api/gamifikasi/poin')->assertOk()->assertJsonCount(0, 'data');
        $this->actingAs($siswa)->getJson('/api/gamifikasi/redemptions')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_siswa_tidak_bisa_mengelola_katalog_reward(): void
    {
        $siswa = User::factory()->siswa()->create();

        $this->actingAs($siswa)->postJson('/api/gamifikasi/rewards', [
            'nama_barang' => 'Reward Palsu',
            'harga_poin' => 1,
            'stok' => 99,
        ])->assertStatus(403);
    }

    public function test_katalog_siswa_hanya_memuat_reward_aktif(): void
    {
        $siswa = User::factory()->siswa()->create();
        Reward::create(['nama_barang' => 'Aktif', 'harga_poin' => 100, 'stok' => 5, 'aktif' => true]);
        Reward::create(['nama_barang' => 'Nonaktif', 'harga_poin' => 100, 'stok' => 5, 'aktif' => false]);

        $response = $this->actingAs($siswa)->getJson('/api/gamifikasi/rewards');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Aktif', $response->json('data.0.nama_barang'));
    }

    public function test_leaderboard_diurutkan_berdasarkan_perolehan_periode(): void
    {
        $siswaA = User::factory()->siswa()->create(['name' => 'Andi']);
        $siswaB = User::factory()->siswa()->create(['name' => 'Budi']);

        PointsLog::create(['user_id' => $siswaA->id, 'sumber' => 'materi', 'jumlah' => 30, 'keterangan' => 'A']);
        PointsLog::create(['user_id' => $siswaB->id, 'sumber' => 'materi', 'jumlah' => 80, 'keterangan' => 'B']);

        $response = $this->actingAs($siswaA)->getJson('/api/gamifikasi/leaderboard?periode=mingguan&lingkup=global');

        $response->assertOk();
        $this->assertSame('Budi', $response->json('data.0.nama'));
        $this->assertSame(80, $response->json('data.0.poin'));
        $this->assertSame(2, $response->json('meta.posisi_saya.peringkat'));
    }

    public function test_login_siswa_memberi_bonus_streak_sekali_sehari(): void
    {
        $siswa = User::factory()->siswa()->create(['nis' => '2024001']);

        $pertama = $this->postJson('/api/login', ['identifier' => '2024001', 'password' => 'password']);
        $pertama->assertOk();
        $this->assertSame(5, $pertama->json('bonus_streak.jumlah'));
        $this->assertSame(1, $pertama->json('bonus_streak.streak_hari'));

        $kedua = $this->postJson('/api/login', ['identifier' => '2024001', 'password' => 'password']);
        $kedua->assertOk();
        $this->assertNull($kedua->json('bonus_streak'));

        $this->assertSame(5, (int) $siswa->fresh()->total_poin);
    }
}
