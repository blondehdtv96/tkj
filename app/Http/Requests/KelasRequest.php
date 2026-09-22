<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tingkat' => ['required', 'integer', 'in:10,11,12'],
            'nama_rombel' => ['required', 'string', 'max:100'],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
