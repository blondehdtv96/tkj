<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedemptionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,completed,rejected'],
            'catatan_petugas' => [$this->input('status') === 'rejected' ? 'required' : 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'catatan_petugas.required' => 'Sertakan alasan penolakan agar siswa memahami keputusannya.',
        ];
    }
}
