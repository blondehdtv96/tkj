<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'min:6'],
            'role' => ['required', 'in:admin,guru,siswa'],
            'username' => ['nullable', 'string', 'max:100', Rule::unique('users', 'username')->ignore($userId)],
            'nis' => ['nullable', 'string', 'max:30', Rule::unique('users', 'nis')->ignore($userId)],
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('users', 'nip')->ignore($userId)],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
        ];
    }
}
