<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MataPelajaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'tingkat' => ['required', 'integer', 'in:10,11,12'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }
}
