<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RewardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_barang' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'harga_poin' => ['required', 'integer', 'min:1', 'max:1000000'],
            'stok' => ['required', 'integer', 'min:0', 'max:100000'],
            'aktif' => ['nullable', 'boolean'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
