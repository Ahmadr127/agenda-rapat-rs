<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_employee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->constrained('agendas')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('signature_image_path')->nullable();
            $table->timestamps();
            $table->unique(['agenda_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_employee');
    }
};
