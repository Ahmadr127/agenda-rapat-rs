<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\AgendaTypeAccess;

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
        // Semua tipe: pukul selesai (bila diisi) harus setelah pukul mulai.
        // Diklat/pelatihan wajib mengisi; rapat opsional tetapi tetap
        // tidak boleh mundur dari pukul mulai.
        $endTimeRule = $this->input('type') === 'rapat'
            ? 'nullable|date_format:H:i|after:event_time'
            : 'nullable|required_if:type,diklat|required_if:type,pelatihan|date_format:H:i|after:event_time';

        $rules = [
            "title" => "required|string|max:255",
            "description" => "nullable|string",
            "event_date" => "required|date",
            "event_time" => "required|date_format:H:i",
            "event_end_time" => $endTimeRule,
            "unit_id" => "required|exists:units,id",
            "event_leader_id" => "required|exists:employees,id",
            "room_id" => "required|exists:rooms,id",
            // Tipe dibatasi permission agendas.type-* milik user
            // (ditambah aturan unit SDM → hanya rapat untuk izin
            // terbatas). Daftar izin kosong berarti tidak ada tipe
            // yang boleh dipilih sehingga validasi selalu gagal.
            "type" => [
                "required",
                Rule::in(AgendaTypeAccess::allowedTypeIds($this->user()) ?: ['__no_allowed_type__']),
            ],
            "bank_soal_id" =>
                "nullable|required_if:type,diklat|required_if:type,pelatihan|exists:bank_soals,id",
            "presenter_ids" => "nullable|array",
            "presenter_ids.*" => "nullable|distinct|exists:employees,id",
            "letter_file" => "nullable|file|mimes:pdf|max:2048",
            "material_file" => "nullable|file|mimes:pdf|max:2048",
            "remove_letter_file" => "nullable|boolean",
            "remove_material_file" => "nullable|boolean",
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
     * Pesan error validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            "type.in" => "Tipe agenda tidak diizinkan untuk akun Anda.",
            "event_time.required" => "Pukul mulai wajib diisi.",
            "event_time.date_format" => "Pukul mulai harus dalam format JJ:MM (24 jam).",
            "event_end_time.required_if" => "Pukul selesai wajib diisi untuk agenda diklat dan pelatihan.",
            "event_end_time.date_format" => "Pukul selesai harus dalam format JJ:MM (24 jam).",
            "event_end_time.after" => "Pukul selesai harus setelah pukul mulai (gunakan format 24 jam, mis. mulai 09:32 maka selesai 12:32 bukan 00:32).",
            "letter_file.max" => "Surat undangan terlalu besar. Maksimal 2 MB.",
            "material_file.max" => "Materi terlalu besar. Maksimal 2 MB.",
            "letter_file.mimes" => "Surat undangan harus berformat PDF.",
            "material_file.mimes" => "Materi harus berformat PDF.",
        ];
    }
}
