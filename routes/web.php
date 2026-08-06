<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BankSoalController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeRecapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAgendaController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\PublicAgendaInputController;
use App\Http\Controllers\PublicQuizController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public agenda list
Route::get("/", [PublicAgendaController::class, "index"])->name("home");

Route::get("/dashboard", function () {
    return view("dashboard");
})
    ->middleware(["auth", "verified"])
    ->name("dashboard");

Route::middleware("auth")->group(function () {
    Route::get("/profile", [ProfileController::class, "edit"])->name(
        "profile.edit",
    );
    Route::patch("/profile", [ProfileController::class, "update"])->name(
        "profile.update",
    );
    Route::delete("/profile", [ProfileController::class, "destroy"])->name(
        "profile.destroy",
    );
});

// Admin routes
Route::middleware("auth")
    ->prefix("admin")
    ->name("admin.")
    ->group(function () {
        Route::get("employees/search", [
            EmployeeController::class,
            "search",
        ])->name("employees.search");
        Route::resource("employees", EmployeeController::class);
        Route::get("employee-recaps", [
            EmployeeRecapController::class,
            "index",
        ])->name("employee-recaps.index");
        Route::get("employee-recaps/export-csv", [
            EmployeeRecapController::class,
            "exportCsv",
        ])->name("employee-recaps.export-csv");
        Route::get("employee-recaps/{employee}/agendas", [
            EmployeeRecapController::class,
            "agendas",
        ])->name("employee-recaps.agendas.index");
        Route::get("employee-recaps/{employee}/agendas/export-csv", [
            EmployeeRecapController::class,
            "exportAgendasCsv",
        ])->name("employee-recaps.agendas.export-csv");
        Route::get("rooms/search", [RoomController::class, "search"])->name(
            "rooms.search",
        );
        Route::resource("rooms", RoomController::class);
        Route::get("agendas/export-csv", [
            AgendaController::class,
            "exportCsv",
        ])->name("agendas.export-csv");
        Route::get("agendas/types/search", [
            AgendaController::class,
            "searchTypes",
        ])->name("agendas.types.search");
        Route::resource("agendas", AgendaController::class);
        Route::get("agendas/{agenda}/export-quiz-csv", [
            AgendaController::class,
            "exportQuizCsv",
        ])->name("agendas.export-quiz-csv");
        Route::get("agendas/{agenda}/export-pdf", [
            AgendaController::class,
            "exportPdf",
        ])->name("agendas.export-pdf");
        Route::get("units/search", [UnitController::class, "search"])->name(
            "units.search",
        );
        Route::resource("units", UnitController::class);
        Route::get("bank-soals/search", [
            BankSoalController::class,
            "search",
        ])->name("bank-soals.search");
        Route::resource("bank-soals", BankSoalController::class);

        // Account management — MANAGER IT only
        Route::middleware('manager_it')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/{user}/change-password', [UserController::class, 'editPassword'])->name('users.change-password');
            Route::put('users/{user}/change-password', [UserController::class, 'updatePassword'])->name('users.update-password');
        });
    });

// Public attendance routes
Route::get("/absen/{agenda}", [AttendanceController::class, "show"])->name(
    "attendance.show",
);
Route::post("/absen/{agenda}/sign", [
    AttendanceController::class,
    "sign",
])->name("attendance.sign");
Route::post("/absen/{agenda}/pretest", [
    AttendanceController::class,
    "storePretest",
])->name("attendance.pretest.store");

// Public quiz routes (posttest)
Route::get("/absen/{agenda}/quiz", [PublicQuizController::class, "show"])->name(
    "attendance.quiz",
);
Route::post("/absen/{agenda}/quiz", [
    PublicQuizController::class,
    "store",
])->name("attendance.quiz.store");

// Public agenda input (crowdsourced notes & images)
Route::get("/agenda/{agenda}/note", [
    PublicAgendaInputController::class,
    "showNote",
])->name("agenda.note.index");
Route::get("/agenda/{agenda}/image", [
    PublicAgendaInputController::class,
    "showImage",
])->name("agenda.image.index");
Route::post("/agenda/{agenda}/note", [
    PublicAgendaInputController::class,
    "storeNote",
])->name("agenda.note.store");
Route::post("/agenda/{agenda}/image", [
    PublicAgendaInputController::class,
    "storeImage",
])->name("agenda.image.store");

require __DIR__ . "/auth.php";
