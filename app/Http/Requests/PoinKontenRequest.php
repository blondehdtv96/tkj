<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Penyimpanan massal poin materi dan soal dari halaman pengaturan poin.
 * Poin null berarti kembali memakai nilai bawaan config/gamifikasi.php.
 */
class PoinKontenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'materi' => ['nullable', 'array'],
            'materi.*.id' => ['required', 'integer', 'exists:materi,id'],
            'materi.*.poin' => ['nullable', 'integer', 'min:0', 'max:1000'],

            'soal' => ['nullable', 'array'],
            'soal.*.id' => ['required', 'integer', 'exists:soal,id'],
            'soal.*.poin' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ];
    }
}
