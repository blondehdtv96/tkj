<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KuisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bab_id' => ['required', 'exists:bab,id'],
            'judul' => ['required', 'string', 'max:200'],
            'durasi_menit' => ['required', 'integer', 'min:1', 'max:300'],
            'kkm' => ['required', 'integer', 'min:0', 'max:100'],
            'acak_soal' => ['boolean'],
            'aktif' => ['boolean'],
        ];
    }
}
