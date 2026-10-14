<?php

namespace App\Services;

use App\Models\Agenda;
use App\Models\AgendaQuestionAnswer;
use Illuminate\Support\Facades\Storage;

/**
 * Operasi daftar hadir terpusat (dipakai halaman admin & publik).
 */
class AttendanceService
{
    /**
     * Hapus satu baris daftar hadir: pivot kehadiran, file tanda tangan,
     * dan jawaban kuis peserta pada agenda ini. False bila baris tak ada.
     */
    public function removeAttendee(Agenda $agenda, int $employeeId): bool
    {
        $pivot = $agenda->employees()->where('employee_id', $employeeId)->first();

        if (! $pivot) {
            return false;
        }

        if ($pivot->pivot->signature_image_path) {
            Storage::disk('public')->delete($pivot->pivot->signature_image_path);
        }

        $agenda->employees()->detach($employeeId);

        AgendaQuestionAnswer::where('agenda_id', $agenda->id)
            ->where('employee_id', $employeeId)
            ->delete();

        return true;
    }
}
