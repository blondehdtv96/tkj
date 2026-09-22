<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PenyesuaianPoinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', 'siswa')],
            'jumlah' => ['required', 'integer', 'not_in:0', 'min:-100000', 'max:100000'],
            'keterangan' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'Penyesuaian poin hanya berlaku untuk akun siswa.',
            'jumlah.not_in' => 'Jumlah penyesuaian tidak boleh nol.',
        ];
    }
}
