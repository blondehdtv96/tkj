<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SoalPemahamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'materi_id' => ['required', 'exists:materi,id'],
            'pertanyaan' => ['required', 'string', 'max:500'],
            'opsi' => ['required', 'array', 'min:2', 'max:5'],
            'opsi.*' => ['required', 'string', 'max:255'],
            'jawaban_benar' => ['required', 'integer', 'min:0', 'lt:'.count((array) $this->input('opsi', []))],
            'urutan' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'jawaban_benar.lt' => 'Kunci jawaban harus menunjuk salah satu opsi yang tersedia.',
        ];
    }
}
