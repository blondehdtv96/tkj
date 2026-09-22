<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BabRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'judul' => ['required', 'string', 'max:200'],
            'urutan' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
