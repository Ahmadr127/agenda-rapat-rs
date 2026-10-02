<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgendaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $agenda = $this->route('agenda');

        return $agenda
            ? $this->user()->can('update', $agenda)
            : $this->user()->can('create', \App\Models\Agenda::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            "title" => "required|string|max:255",
            "description" => "nullable|string",
            "event_date" => "required|date",
            "event_time" => "required|date_format:H:i",
            "event_end_time" =>
                "nullable|required_if:type,diklat|required_if:type,pelatihan|date_format:H:i|after:event_time",
            "unit_id" => "required|exists:units,id",
            "event_leader_id" => "required|exists:employees,id",
            "room_id" => "required|exists:rooms,id",
            "type" => "required|in:diklat,pelatihan,rapat",
            "bank_soal_id" =>
                "nullable|required_if:type,diklat|required_if:type,pelatihan|exists:bank_soals,id",
            "presenter_ids" => "nullable|array",
            "presenter_ids.*" => "nullable|distinct|exists:employees,id",
            "letter_file" => "nullable|file|mimes:pdf|max:2048",
            "material_file" => "nullable|file|mimes:pdf|max:2048",
        ];

        // Pemegang izin terbatas wajib memakai unitnya sendiri — menutup
        // lubang manipulasi unit_id lewat request buatan.
        if (! $this->user()->can('agendas.manage-all')) {
            $rules["unit_id"] = [
                "required",
                "exists:units,id",
                Rule::in([$this->user()->unitId()]),
            ];
        }

        return $rules;
    }

    /**
     * Aturan bisnis unit SDM (hanya boleh tipe rapat) ditegakkan di
     * server, bukan sekadar disembunyikan di form.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->user()->can('agendas.manage-all')) {
                return;
            }

            $unitName = $this->user()->employee?->unit?->name;

            if ($unitName === 'SDM' && $this->input('type') !== 'rapat') {
                $validator->errors()->add(
                    'type',
                    'Unit SDM hanya dapat membuat agenda bertipe Rapat.',
                );
            }
        });
    }

    /**
     * Pesan error validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            "letter_file.max" => "Surat undangan terlalu besar. Maksimal 2 MB.",
            "material_file.max" => "Materi terlalu besar. Maksimal 2 MB.",
            "letter_file.mimes" => "Surat undangan harus berformat PDF.",
            "material_file.mimes" => "Materi harus berformat PDF.",
        ];
    }
}
