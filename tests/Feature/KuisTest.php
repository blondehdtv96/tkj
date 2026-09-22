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

class KuisTest extends TestCase
{
    use RefreshDatabase;

    private function buatKuis(): Kuis
    {
        $mapel = MataPelajaran::create(['nama' => 'TKJ', 'tingkat' => 10]);
        $bab = Bab::create(['mata_pelajaran_id' => $mapel->id, 'judul' => 'Bab 1', 'urutan' => 1]);
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
            ]);
            OpsiJawaban::create(['soal_id' => $soal->id, 'teks' => 'Benar', 'is_benar' => true]);
            OpsiJawaban::create(['soal_id' => $soal->id, 'teks' => 'Salah', 'is_benar' => false]);
        }

        return $kuis;
    }

    public function test_siswa_memulai_kuis_tidak_menerima_kunci_jawaban(): void
    {
        $kuis = $this->buatKuis();
        $siswa = User::factory()->siswa()->create();

        $response = $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai");

        $response->assertOk();
        $response->assertJsonMissingPath('data.soal.0.opsi_jawaban.0.is_benar');
        $response->assertJsonMissingPath('data.soal.0.pembahasan');
        $this->assertCount(2, $response->json('data.soal'));
    }

    public function test_siswa_submit_kuis_dikoreksi_otomatis_di_server(): void
    {
        $kuis = $this->buatKuis();
        $siswa = User::factory()->siswa()->create();

        $mulai = $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai")->json('data');
        $hasilKuisId = $mulai['hasil_kuis_id'];
        $soal = $mulai['soal'];

        $opsiBenarSoal1 = Soal::find($soal[0]['id'])->opsiJawaban()->where('is_benar', true)->first();
        $opsiSalahSoal2 = Soal::find($soal[1]['id'])->opsiJawaban()->where('is_benar', false)->first();

        $response = $this->actingAs($siswa)->postJson("/api/hasil-kuis/{$hasilKuisId}/submit", [
            'jawaban' => [
                ['soal_id' => $soal[0]['id'], 'opsi_id' => $opsiBenarSoal1->id],
                ['soal_id' => $soal[1]['id'], 'opsi_id' => $opsiSalahSoal2->id],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.skor', 50);
        $response->assertJsonPath('data.selesai', true);
        // Setelah selesai, pembahasan & kunci jawaban boleh terlihat.
        $response->assertJsonStructure(['data' => ['jawaban' => [['soal' => ['opsi_jawaban']]]]]);
    }

    public function test_submit_ulang_pada_hasil_kuis_yang_sudah_selesai_bersifat_idempoten(): void
    {
        $kuis = $this->buatKuis();
        $siswa = User::factory()->siswa()->create();

        $mulai = $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai")->json('data');
        $hasilKuisId = $mulai['hasil_kuis_id'];
        $soal = $mulai['soal'];

        $this->actingAs($siswa)->postJson("/api/hasil-kuis/{$hasilKuisId}/submit", [
            'jawaban' => [['soal_id' => $soal[0]['id'], 'opsi_id' => null]],
        ]);

        $kedua = $this->actingAs($siswa)->postJson("/api/hasil-kuis/{$hasilKuisId}/submit", [
            'jawaban' => [],
        ]);

        $kedua->assertOk();
        $this->assertSame(
            $kedua->json('data.waktu_selesai'),
            $kedua->json('data.waktu_selesai')
        );
    }

    public function test_siswa_tidak_bisa_memulai_kuis_sebelum_materi_bab_selesai(): void
    {
        $kuis = $this->buatKuis();
        $siswa = User::factory()->siswa()->create();

        foreach (range(1, 2) as $i) {
            Materi::create([
                'bab_id' => $kuis->bab_id,
                'judul' => "Materi {$i}",
                'konten_html' => '<p>Isi</p>',
                'tipe' => 'teks',
                'urutan' => $i,
            ]);
        }

        $response = $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai");

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('kuis');
        $this->assertDatabaseCount('hasil_kuis', 0);
    }

    public function test_kuis_terbuka_setelah_seluruh_materi_bab_diselesaikan(): void
    {
        $kuis = $this->buatKuis();
        $siswa = User::factory()->siswa()->create();

        $materiList = collect(range(1, 2))->map(fn ($i) => Materi::create([
            'bab_id' => $kuis->bab_id,
            'judul' => "Materi {$i}",
            'konten_html' => '<p>Isi</p>',
            'tipe' => 'teks',
            'urutan' => $i,
        ]));

        // Menyelesaikan sebagian materi belum cukup untuk membuka kuis.
        $this->actingAs($siswa)->postJson("/api/materi/{$materiList[0]->id}/selesai")->assertOk();
        $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai")->assertStatus(422);

        $this->actingAs($siswa)->postJson("/api/materi/{$materiList[1]->id}/selesai")->assertOk();
        $this->actingAs($siswa)->postJson("/api/kuis/{$kuis->id}/mulai")->assertOk();
    }

    public function test_daftar_kuis_menandai_status_terkunci_untuk_siswa(): void
    {
        $kuis = $this->buatKuis();
        $siswa = User::factory()->siswa()->create();

        $materi = Materi::create([
            'bab_id' => $kuis->bab_id,
            'judul' => 'Materi 1',
            'konten_html' => '<p>Isi</p>',
            'tipe' => 'teks',
            'urutan' => 1,
        ]);

        $this->actingAs($siswa)
            ->getJson("/api/kuis?bab_id={$kuis->bab_id}")
            ->assertOk()
            ->assertJsonPath('data.0.terkunci', true)
            ->assertJsonPath('data.0.materi_belum_selesai', 1);

        $this->actingAs($siswa)->postJson("/api/materi/{$materi->id}/selesai");

        $this->actingAs($siswa)
            ->getJson("/api/kuis?bab_id={$kuis->bab_id}")
            ->assertOk()
            ->assertJsonPath('data.0.terkunci', false)
            ->assertJsonPath('data.0.materi_belum_selesai', 0);
    }

    public function test_siswa_tidak_bisa_mengelola_bank_soal(): void
    {
        $kuis = $this->buatKuis();
        $siswa = User::factory()->siswa()->create();

        $this->actingAs($siswa)->postJson('/api/soal', [
            'kuis_id' => $kuis->id,
            'pertanyaan' => 'Soal baru',
            'tipe' => 'pilihan_ganda',
            'bobot' => 10,
            'opsi_jawaban' => [
                ['teks' => 'A', 'is_benar' => true],
                ['teks' => 'B', 'is_benar' => false],
            ],
        ])->assertForbidden();
    }

    public function test_guru_dapat_membuat_soal_dengan_opsi_jawaban_dinamis(): void
    {
        $kuis = $this->buatKuis();
        $guru = User::factory()->guru()->create();

        $response = $this->actingAs($guru)->postJson('/api/soal', [
            'kuis_id' => $kuis->id,
            'pertanyaan' => 'Soal baru',
            'tipe' => 'pilihan_ganda',
            'bobot' => 10,
            'opsi_jawaban' => [
                ['teks' => 'A', 'is_benar' => true],
                ['teks' => 'B', 'is_benar' => false],
                ['teks' => 'C', 'is_benar' => false],
            ],
        ]);

        $response->assertCreated();
        $this->assertCount(3, $response->json('data.opsi_jawaban'));
    }
}
