<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Jawaban kuis pemahaman bersifat opsional pada level validasi: materi yang
 * tidak memiliki soal pemahaman tetap dapat ditandai selesai tanpa payload.
 * Kelengkapan dan kebenarannya diperiksa PemahamanService.
 */
class MateriSelesaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jawaban' => ['nullable', 'array'],
            'jawaban.*.soal_id' => ['required', 'integer', 'exists:soal_pemahaman,id'],
            'jawaban.*.jawaban' => ['required', 'integer', 'min:0'],
        ];
    }
}
