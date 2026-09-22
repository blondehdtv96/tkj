<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReferensiBelajarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bab_id' => ['required', 'exists:bab,id'],
            'ringkasan' => ['nullable', 'string'],
            'video_judul' => ['nullable', 'string', 'max:200'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'kasus_soal' => ['nullable', 'string'],
        ];
    }
}
