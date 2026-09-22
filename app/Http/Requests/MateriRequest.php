<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MateriRequest extends FormRequest
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
            'tipe' => ['required', 'in:teks,video,pdf'],
            'konten_html' => ['required_if:tipe,teks', 'nullable', 'string'],
            'urutan' => ['nullable', 'integer', 'min:1'],
            'poin' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'file' => [
                'nullable',
                'file',
                'max:51200',
                'mimes:pdf,mp4,webm,mov,jpg,jpeg,png',
            ],
        ];
    }
}
