<?php

namespace Tests\Feature;

use App\Models\Bab;
use App\Models\Kuis;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\OpsiJawaban;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoalTroubleshootingTest extends TestCase
{
    use RefreshDatabase;

    private Bab $bab;

    private Materi $materi;

    protected function setUp(): void
    {
        parent::setUp();

        $mapel = MataPelajaran::create(['nama' => 'TKJ', 'tingkat' => 10]);
        $this->bab = Bab::create(['mata_pelajaran_id' => $mapel->id, 'judul' => 'Perakitan PC', 'urutan' => 1]);
        $this->materi = Materi::create([
            'bab_id' => $this->bab->id,
            'judul' => 'Pengantar Perakitan PC',
            'konten_html' => '<p>Isi</p>',
            'tipe' => 'teks',
            'urutan' => 1,
        ]);
    }

    private function buatKuis(): Kuis
    {
        return Kuis::create([
            'bab_id' => $this->bab->id,
            'judul' => 'Kuis Perakitan',
            'durasi_menit' => 20,
            'kkm' => 75,
            'acak_soal' => false,
            'aktif' => true,
        ]);
    }

    private function buatSoalTroubleshooting(Kuis $kuis, int $poin = 40): Soal
    {
        $soal = Soal::create([
            'kuis_id' => $kuis->id,
            'pertanyaan' => 'Susun langkah penanganan yang benar.',
            'skenario' => 'PC baru dirakit tidak menyala sama sekali.',
            'tipe' => 'troubleshooting',
            'bobot' => 20,
            'poin' => $poin,
        ]);

        foreach (['Cek kabel power', 'Cek konektor 24-pin', 'Tes PSU', 'Cek RAM'] as $i => $teks) {
            OpsiJawaban::create([
                'soal_id' => $soal->id,
                'teks' => $teks,
                'is_benar' => true,
                'urutan' => $i + 1,
            ]);
        }

        return $soal;
    }

    private function siswaSiapKuis(): User
    {
        $siswa = User::factory()->siswa()->create();
        $this->actingAs($siswa)->postJson("/api/materi/{$this->materi->id}/selesai")->assertOk();

        return $siswa;
    }

    public function test_admin_dapat_membuat_soal_troubleshooting(): void
    {
        $admin = User::factory()->admin()->create();
        $kuis = $this->buatKuis();

        $response = $this->actingAs($admin)->postJson('/api/soal', [
            'kuis_id' => $kuis->id,
            'pertanyaan' => 'Susun langkah penanganan yang benar.',
            'skenario' => 'Jaringan kantor tiba-tiba tidak bisa akses internet.',
            'tipe' => 'troubleshooting',
            'bobot' => 20,
            'poin' => 30,
            'opsi_jawaban' => [
                ['teks' => 'Cek lampu indikator modem', 'is_benar' => true, 'urutan' => 1],
                ['teks' => 'Ping gateway', 'is_benar' => true, 'urutan' => 2],
                ['teks' => 'Ping DNS publik', 'is_benar' => true, 'urutan' => 3],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.tipe', 'troubleshooting');
        $response->assertJsonPath('data.poin', 30);
        $response->assertJsonPath('data.skenario', 'Jaringan kantor tiba-tiba tidak bisa akses internet.');
        $this->assertSame([1, 2, 3], collect($response->json('data.opsi_jawaban'))->pluck('urutan')->all());
    }

    public function test_admin_dapat_membuat_soal_essay_dan_benar_salah(): void
    {
        $admin = User::factory()->admin()->create();
        $kuis = $this->buatKuis();

        $this->actingAs($admin)->postJson('/api/soal', [
            'kuis_id' => $kuis->id,
            'pertanyaan' => 'Jelaskan fungsi power supply.',
            'tipe' => 'essay',
            'bobot' => 10,
        ])->assertStatus(201)->assertJsonPath('data.tipe', 'essay');

        $this->actingAs($admin)->postJson('/api/soal', [
            'kuis_id' => $kuis->id,
            'pertanyaan' => 'RAM termasuk penyimpanan permanen.',
            'tipe' => 'benar_salah',
            'bobot' => 5,
            'opsi_jawaban' => [
                ['teks' => 'Benar', 'is_benar' => false],
                ['teks' => 'Salah', 'is_benar' => true],
            ],
        ])->assertStatus(201)->assertJsonPath('data.tipe', 'benar_salah');
    }

    public function test_urutan_langkah_troubleshooting_wajib_lengkap_dan_berurutan(): void
    {
        $admin = User::factory()->admin()->create();
        $kuis = $this->buatKuis();

        // Ada langkah tanpa nomor urutan.
        $this->actingAs($admin)->postJson('/api/soal', [
            'kuis_id' => $kuis->id,
            'pertanyaan' => 'Susun langkah.',
            'tipe' => 'troubleshooting',
            'bobot' => 10,
            'opsi_jawaban' => [
                ['teks' => 'Langkah A', 'is_benar' => true, 'urutan' => 1],
                ['teks' => 'Langkah B', 'is_benar' => true],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors('opsi_jawaban');

        // Nomor urutan berulang.
        $this->actingAs($admin)->postJson('/api/soal', [
            'kuis_id' => $kuis->id,
            'pertanyaan' => 'Susun langkah.',
            'tipe' => 'troubleshooting',
            'bobot' => 10,
            'opsi_jawaban' => [
                ['teks' => 'Langkah A', 'is_benar' => true, 'urutan' => 1],
                ['teks' => 'Langkah B', 'is_benar' => true, 'urutan' => 1],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors('opsi_jawaban');
    }

    public function test_siswa_tidak_menerima_kunci_urutan_langkah(): void
    {
        $kuis = $this->buatKuis();
        $this->buatSoalTroubleshooting($kuis);
        $siswa = $this->siswaSiapKuis();

        $response = $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai");

        $response->assertOk();
        $response->assertJsonMissingPath('data.soal.0.opsi_jawaban.0.urutan');
        $response->assertJsonMissingPath('data.soal.0.opsi_jawaban.0.is_benar');
        $response->assertJsonPath('data.soal.0.skenario', 'PC baru dirakit tidak menyala sama sekali.');
        $this->assertCount(4, $response->json('data.soal.0.opsi_jawaban'));
    }

    public function test_urutan_benar_seluruhnya_memberi_skor_dan_poin_penuh(): void
    {
        $kuis = $this->buatKuis();
        $soal = $this->buatSoalTroubleshooting($kuis, poin: 40);
        $siswa = $this->siswaSiapKuis();
        $poinAwal = (int) $siswa->fresh()->total_poin;

        $mulai = $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai");
        $hasilKuisId = $mulai->json('data.hasil_kuis_id');

        $urutanBenar = $soal->opsiJawaban()->orderBy('urutan')->pluck('id')->all();

        $response = $this->actingAs($siswa)->postJson("/api/hasil-kuis/{$hasilKuisId}/submit", [
            'jawaban' => [['soal_id' => $soal->id, 'jawaban_urutan' => $urutanBenar]],
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.skor', 100);
        $response->assertJsonPath('data.jawaban.0.is_benar', true);

        // 40 poin soal + 20 bonus KKM + 15 bonus tepat waktu.
        $this->assertSame(75, $response->json('gamifikasi.poin_diperoleh'));
        $this->assertSame($poinAwal + 75, (int) $siswa->fresh()->total_poin);
    }

    public function test_urutan_sebagian_benar_memberi_skor_dan_poin_sebagian(): void
    {
        $kuis = $this->buatKuis();
        $soal = $this->buatSoalTroubleshooting($kuis, poin: 40);
        $siswa = $this->siswaSiapKuis();
        $poinAwal = (int) $siswa->fresh()->total_poin;

        $mulai = $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai");
        $hasilKuisId = $mulai->json('data.hasil_kuis_id');

        $urutan = $soal->opsiJawaban()->orderBy('urutan')->pluck('id')->all();
        // Dua langkah terakhir tertukar: 2 dari 4 posisi tetap benar.
        [$urutan[2], $urutan[3]] = [$urutan[3], $urutan[2]];

        $response = $this->actingAs($siswa)->postJson("/api/hasil-kuis/{$hasilKuisId}/submit", [
            'jawaban' => [['soal_id' => $soal->id, 'jawaban_urutan' => $urutan]],
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.skor', 50);
        $response->assertJsonPath('data.jawaban.0.is_benar', false);
        $response->assertJsonPath('data.jawaban.0.porsi_benar', 0.5);

        // 40 x 0.5 = 20 poin soal + 15 bonus tepat waktu, tanpa bonus KKM.
        $this->assertSame(35, $response->json('gamifikasi.poin_diperoleh'));
        $this->assertSame($poinAwal + 35, (int) $siswa->fresh()->total_poin);
    }

    public function test_tidak_menjawab_soal_troubleshooting_bernilai_nol(): void
    {
        $kuis = $this->buatKuis();
        $soal = $this->buatSoalTroubleshooting($kuis);
        $siswa = $this->siswaSiapKuis();

        $mulai = $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai");
        $hasilKuisId = $mulai->json('data.hasil_kuis_id');

        $response = $this->actingAs($siswa)->postJson("/api/hasil-kuis/{$hasilKuisId}/submit", [
            'jawaban' => [['soal_id' => $soal->id, 'jawaban_urutan' => []]],
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.skor', 0);
        // JSON menuliskan 0.0 sebagai 0, jadi dibandingkan setelah dicor ke float.
        $this->assertSame(0.0, (float) $response->json('data.jawaban.0.porsi_benar'));
        $this->assertSame(0, $response->json('gamifikasi.poin_diperoleh'));
    }

    public function test_mengulang_kuis_tidak_menggandakan_poin(): void
    {
        $kuis = $this->buatKuis();
        $soal = Soal::create([
            'kuis_id' => $kuis->id,
            'pertanyaan' => 'Soal ulang',
            'tipe' => 'pilihan_ganda',
            'bobot' => 10,
            'poin' => 30,
        ]);
        $benar = OpsiJawaban::create(['soal_id' => $soal->id, 'teks' => 'Benar', 'is_benar' => true]);
        OpsiJawaban::create(['soal_id' => $soal->id, 'teks' => 'Salah', 'is_benar' => false]);

        $siswa = $this->siswaSiapKuis();
        $poinAwal = (int) $siswa->fresh()->total_poin;

        foreach (range(1, 3) as $percobaan) {
            $mulai = $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai");
            $this->actingAs($siswa)->postJson("/api/hasil-kuis/{$mulai->json('data.hasil_kuis_id')}/submit", [
                'jawaban' => [['soal_id' => $soal->id, 'opsi_id' => $benar->id]],
            ])->assertOk();
        }

        // 30 poin soal + 20 bonus KKM + 15 bonus tepat waktu, hanya sekali.
        $this->assertSame($poinAwal + 65, (int) $siswa->fresh()->total_poin);
    }

    public function test_poin_soal_mengikuti_pengaturan_admin(): void
    {
        $kuis = $this->buatKuis();
        $soal = Soal::create([
            'kuis_id' => $kuis->id,
            'pertanyaan' => 'Soal bernilai besar',
            'tipe' => 'pilihan_ganda',
            'bobot' => 10,
            'poin' => 120,
        ]);
        $benar = OpsiJawaban::create(['soal_id' => $soal->id, 'teks' => 'Benar', 'is_benar' => true]);
        OpsiJawaban::create(['soal_id' => $soal->id, 'teks' => 'Salah', 'is_benar' => false]);

        $siswa = $this->siswaSiapKuis();
        $poinAwal = (int) $siswa->fresh()->total_poin;

        $mulai = $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai");
        $hasilKuisId = $mulai->json('data.hasil_kuis_id');

        $response = $this->actingAs($siswa)->postJson("/api/hasil-kuis/{$hasilKuisId}/submit", [
            'jawaban' => [['soal_id' => $soal->id, 'opsi_id' => $benar->id]],
        ]);

        // 120 poin soal + 20 bonus KKM + 15 bonus tepat waktu.
        $this->assertSame(155, $response->json('gamifikasi.poin_diperoleh'));
        $this->assertSame($poinAwal + 155, (int) $siswa->fresh()->total_poin);
    }

    public function test_soal_tanpa_poin_memakai_nilai_bawaan(): void
    {
        $kuis = $this->buatKuis();
        $soal = Soal::create([
            'kuis_id' => $kuis->id,
            'pertanyaan' => 'Soal tanpa pengaturan poin',
            'tipe' => 'pilihan_ganda',
            'bobot' => 10,
        ]);
        $benar = OpsiJawaban::create(['soal_id' => $soal->id, 'teks' => 'Benar', 'is_benar' => true]);
        OpsiJawaban::create(['soal_id' => $soal->id, 'teks' => 'Salah', 'is_benar' => false]);

        $siswa = $this->siswaSiapKuis();
        $mulai = $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai");

        $response = $this->actingAs($siswa)->postJson("/api/hasil-kuis/{$mulai->json('data.hasil_kuis_id')}/submit", [
            'jawaban' => [['soal_id' => $soal->id, 'opsi_id' => $benar->id]],
        ]);

        $bawaan = (int) config('gamifikasi.poin.soal_default');
        $this->assertSame($bawaan + 20 + 15, $response->json('gamifikasi.poin_diperoleh'));
    }

    public function test_poin_materi_mengikuti_pengaturan_admin(): void
    {
        $this->materi->update(['poin' => 75]);
        $siswa = User::factory()->siswa()->create();

        $response = $this->actingAs($siswa)->postJson("/api/materi/{$this->materi->id}/selesai");

        $response->assertOk();
        // 75 poin materi + 50 bonus bab tuntas.
        $this->assertSame(125, $response->json('poin_diperoleh'));
    }

    public function test_admin_dapat_melihat_dan_menyimpan_pengaturan_poin_konten(): void
    {
        $admin = User::factory()->admin()->create();
        $kuis = $this->buatKuis();
        $soal = $this->buatSoalTroubleshooting($kuis, poin: 40);

        $lihat = $this->actingAs($admin)->getJson("/api/poin-konten?bab_id={$this->bab->id}");

        $lihat->assertOk();
        $lihat->assertJsonPath('data.bab.id', $this->bab->id);
        $lihat->assertJsonPath('data.materi.0.poin_efektif', (int) config('gamifikasi.poin.materi_selesai'));
        $lihat->assertJsonPath('data.kuis.0.total_poin', 40);

        $simpan = $this->actingAs($admin)->postJson('/api/poin-konten', [
            'materi' => [['id' => $this->materi->id, 'poin' => 30]],
            'soal' => [['id' => $soal->id, 'poin' => 90]],
        ]);

        $simpan->assertOk();
        $this->assertSame(30, (int) $this->materi->fresh()->poin);
        $this->assertSame(90, (int) $soal->fresh()->poin);
    }

    public function test_poin_konten_dikosongkan_kembali_ke_nilai_bawaan(): void
    {
        $admin = User::factory()->admin()->create();
        $this->materi->update(['poin' => 99]);

        $this->actingAs($admin)->postJson('/api/poin-konten', [
            'materi' => [['id' => $this->materi->id, 'poin' => null]],
        ])->assertOk();

        $this->assertNull($this->materi->fresh()->poin);
        $this->assertSame(
            (int) config('gamifikasi.poin.materi_selesai'),
            $this->materi->fresh()->poinGamifikasi()
        );
    }

    public function test_siswa_tidak_bisa_mengubah_pengaturan_poin(): void
    {
        $siswa = User::factory()->siswa()->create();

        $this->actingAs($siswa)
            ->getJson("/api/poin-konten?bab_id={$this->bab->id}")
            ->assertStatus(403);

        $this->actingAs($siswa)->postJson('/api/poin-konten', [
            'materi' => [['id' => $this->materi->id, 'poin' => 999]],
        ])->assertStatus(403);

        $this->assertNull($this->materi->fresh()->poin);
    }
}
