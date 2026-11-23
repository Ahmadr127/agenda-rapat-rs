<?php

namespace App\Support;

class AgendaNote
{
    /**
     * Aturan validasi notulensi rapat (satu aturan untuk admin & publik).
     *
     * @return array<string, string|array<string, mixed>>
     */
    public static function rules(): array
    {
        return [
            'topic' => 'required|string|max:255',
            'decision' => 'required|string',
            'remarks' => 'nullable|string',
            'pj' => 'required|string|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'topic.required' => 'Topik wajib diisi.',
            'decision.required' => 'Pembahasan wajib diisi.',
            'pj.required' => 'Penanggung Jawab wajib diisi.',
            'max' => ':attribute tidak boleh lebih dari :max karakter.',
            'string' => ':attribute harus berupa teks.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(): array
    {
        return [
            'topic' => 'Topik',
            'decision' => 'Pembahasan',
            'remarks' => 'Kesimpulan',
            'pj' => 'Penanggung Jawab',
        ];
    }
}
