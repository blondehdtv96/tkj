<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedemptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reward_id' => ['required', 'exists:rewards,id'],
            'catatan_siswa' => ['nullable', 'string', 'max:255'],
        ];
    }
}
