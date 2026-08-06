<?php

namespace App\Http\Controllers;

use App\Models\Agenda;

class PublicAgendaController extends Controller
{
    public function index()
    {
        $agendas = Agenda::with(["room", "unit"])
            ->whereDate("event_date", today())
            ->withCount([
                "employees as signed_count" => function ($query) {
                    $query->whereNotNull(
                        "agenda_employee.signature_image_path",
                    );
                },
            ])
            ->orderBy("event_time")
            ->get();

        return view("public.agenda-today", compact("agendas"));
    }
}
