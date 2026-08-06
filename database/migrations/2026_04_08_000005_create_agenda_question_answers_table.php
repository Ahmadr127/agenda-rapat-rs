<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->constrained('agendas')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('agenda_question_id')->constrained('agenda_questions')->cascadeOnDelete();
            $table->enum('selected_option', ['a', 'b', 'c', 'd', 'e']);
            $table->boolean('is_correct');
            $table->timestamps();

            $table->unique(['agenda_question_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_question_answers');
    }
};
