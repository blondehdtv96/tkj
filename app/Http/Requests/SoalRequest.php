<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kuis_id' => ['required', 'exists:kuis,id'],
            'pertanyaan' => ['required', 'string'],
            'skenario' => ['nullable', 'string', 'max:2000'],
            'tipe' => ['required', 'in:pilihan_ganda,essay,benar_salah,troubleshooting'],
            'pembahasan' => ['nullable', 'string'],
            'bobot' => ['required', 'integer', 'min:1'],
            'poin' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'gambar' => ['nullable', 'image', 'max:5120'],
            'opsi_jawaban' => ['required_unless:tipe,essay', 'array', 'min:2'],
            'opsi_jawaban.*.teks' => ['required', 'string', 'max:255'],
            'opsi_jawaban.*.is_benar' => ['boolean'],
            'opsi_jawaban.*.urutan' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $tipe = $this->input('tipe');

            if ($tipe === 'essay') {
                return;
            }

            $opsi = collect($this->input('opsi_jawaban', []));

            if ($tipe === 'troubleshooting') {
                $this->periksaLangkah($validator, $opsi);

                return;
            }

            if ($opsi->where('is_benar', true)->count() !== 1) {
                $validator->errors()->add(
                    'opsi_jawaban',
                    'Tepat satu opsi jawaban harus ditandai sebagai benar.'
                );
            }
        });
    }

    /**
     * Soal troubleshooting memakai kolom urutan sebagai kunci jawaban: setiap
     * langkah wajib punya posisi, dan posisinya harus 1..n tanpa duplikat.
     */
    private function periksaLangkah(Validator $validator, \Illuminate\Support\Collection $opsi): void
    {
        $urutan = $opsi->pluck('urutan')->map(fn ($nilai) => $nilai === null ? null : (int) $nilai);

        if ($urutan->contains(null)) {
            $validator->errors()->add('opsi_jawaban', 'Setiap langkah penanganan harus memiliki nomor urutan.');

            return;
        }

        $diharapkan = range(1, $opsi->count());

        if ($urutan->sort()->values()->all() !== $diharapkan) {
            $validator->errors()->add(
                'opsi_jawaban',
                'Nomor urutan langkah harus berurutan dari 1 sampai '.$opsi->count().' tanpa pengulangan.'
            );
        }
    }
}
