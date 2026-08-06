<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AgendaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
            "letter_file" => "nullable|file|mimes:pdf|max:500",
            "material_file" => "nullable|file|mimes:pdf|max:10240",
        ];
    }
}
