<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BabController;
use App\Http\Controllers\Api\HasilKuisController;
use App\Http\Controllers\Api\KelasController;
use App\Http\Controllers\Api\KuisAttemptController;
use App\Http\Controllers\Api\KuisController;
use App\Http\Controllers\Api\LaporanExportController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\MataPelajaranController;
use App\Http\Controllers\Api\MateriController;
use App\Http\Controllers\Api\PoinController;
use App\Http\Controllers\Api\PoinKontenController;
use App\Http\Controllers\Api\RedemptionController;
use App\Http\Controllers\Api\ReferensiBelajarController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\SoalController;
use App\Http\Controllers\Api\SoalPemahamanController;
use App\Http\Controllers\Api\StatistikController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Konten pembelajaran - dapat dibaca semua role yang login.
    Route::get('/mata-pelajaran', [MataPelajaranController::class, 'index']);
    Route::get('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'show']);
    Route::get('/bab', [BabController::class, 'index']);
    Route::get('/bab/{bab}', [BabController::class, 'show']);
    Route::get('/materi', [MateriController::class, 'index']);
    Route::get('/materi/{materi}', [MateriController::class, 'show']);
    Route::get('/kuis', [KuisController::class, 'index']);
    Route::get('/kelas', [KelasController::class, 'index']);
    Route::get('/referensi-belajar', [ReferensiBelajarController::class, 'index']);
    Route::get('/soal-pemahaman', [SoalPemahamanController::class, 'index']);

    // Gamifikasi - dapat dibaca semua role yang login.
    Route::prefix('gamifikasi')->group(function () {
        Route::get('/ringkasan', [PoinController::class, 'ringkasan']);
        Route::get('/poin', [PoinController::class, 'riwayat']);
        Route::get('/leaderboard', [LeaderboardController::class, 'index']);
        Route::get('/rewards', [RewardController::class, 'index']);
        Route::get('/redemptions', [RedemptionController::class, 'index']);
        Route::get('/redemptions/{redemption}', [RedemptionController::class, 'show']);
    });

    // Siswa: pengerjaan kuis & progres materi.
    Route::middleware('role:siswa')->group(function () {
        Route::post('/materi/{materi}/selesai', [MateriController::class, 'tandaiSelesai']);
        Route::post('/kuis/{kuis}/mulai', [KuisAttemptController::class, 'mulai']);
        Route::post('/hasil-kuis/{hasilKuis}/submit', [KuisAttemptController::class, 'submit']);

        // Pengajuan penukaran reward, poin langsung ditahan.
        Route::post('/gamifikasi/redemptions', [RedemptionController::class, 'store']);
    });

    // Nilai: siswa lihat milik sendiri, guru/admin lihat & filter semua.
    Route::get('/hasil-kuis', [HasilKuisController::class, 'index']);
    Route::get('/hasil-kuis/{hasilKuis}', [HasilKuisController::class, 'show']);

    // Guru & admin: kelola konten pembelajaran, bank soal, dan laporan.
    Route::middleware('role:guru,admin')->group(function () {
        Route::get('/kuis/{kuis}', [KuisController::class, 'show']);
        Route::get('/soal', [SoalController::class, 'index']);
        Route::post('/soal', [SoalController::class, 'store']);
        Route::post('/soal/{soal}', [SoalController::class, 'update']);
        Route::delete('/soal/{soal}', [SoalController::class, 'destroy']);

        Route::post('/mata-pelajaran', [MataPelajaranController::class, 'store']);
        Route::put('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'update']);
        Route::delete('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'destroy']);

        Route::post('/bab', [BabController::class, 'store']);
        Route::put('/bab/{bab}', [BabController::class, 'update']);
        Route::delete('/bab/{bab}', [BabController::class, 'destroy']);

        Route::post('/materi', [MateriController::class, 'store']);
        Route::post('/materi/{materi}', [MateriController::class, 'update']);
        Route::delete('/materi/{materi}', [MateriController::class, 'destroy']);

        Route::post('/kuis', [KuisController::class, 'store']);
        Route::put('/kuis/{kuis}', [KuisController::class, 'update']);
        Route::delete('/kuis/{kuis}', [KuisController::class, 'destroy']);

        // Pengaturan poin gamifikasi per materi dan per soal dalam satu bab.
        Route::get('/poin-konten', [PoinKontenController::class, 'index']);
        Route::post('/poin-konten', [PoinKontenController::class, 'update']);

        Route::get('/statistik', [StatistikController::class, 'index']);
        Route::get('/laporan/export-nilai', [LaporanExportController::class, 'exportNilai']);

        Route::post('/soal-pemahaman', [SoalPemahamanController::class, 'store']);
        Route::put('/soal-pemahaman/{soalPemahaman}', [SoalPemahamanController::class, 'update']);
        Route::delete('/soal-pemahaman/{soalPemahaman}', [SoalPemahamanController::class, 'destroy']);

        // Kelola katalog reward dan verifikasi penukaran poin.
        Route::post('/gamifikasi/rewards', [RewardController::class, 'store']);
        Route::post('/gamifikasi/rewards/{reward}', [RewardController::class, 'update']);
        Route::delete('/gamifikasi/rewards/{reward}', [RewardController::class, 'destroy']);
        Route::post('/gamifikasi/redemptions/{redemption}/status', [RedemptionController::class, 'ubahStatus']);
        Route::post('/gamifikasi/poin/penyesuaian', [PoinController::class, 'penyesuaian']);
    });

    // Admin: kelola kelas dan pengguna.
    Route::middleware('role:admin')->group(function () {
        Route::post('/kelas', [KelasController::class, 'store']);
        Route::put('/kelas/{kelas}', [KelasController::class, 'update']);
        Route::delete('/kelas/{kelas}', [KelasController::class, 'destroy']);

        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);

        Route::post('/referensi-belajar', [ReferensiBelajarController::class, 'store']);
        Route::delete('/referensi-belajar/{referensiBelajar}', [ReferensiBelajarController::class, 'destroy']);
    });
});
