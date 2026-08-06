<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_question_answers', function (Blueprint $table) {
            $table->enum('quiz_type', ['pretest', 'posttest'])->default('pretest')->after('is_correct');

            // Drop old unique constraint and add new one that includes quiz_type
            $table->dropUnique(['agenda_question_id', 'employee_id']);
            $table->unique(['agenda_question_id', 'employee_id', 'quiz_type']);
        });
    }

    public function down(): void
    {
        Schema::table('agenda_question_answers', function (Blueprint $table) {
            $table->dropUnique(['agenda_question_id', 'employee_id', 'quiz_type']);
            $table->unique(['agenda_question_id', 'employee_id']);
            $table->dropColumn('quiz_type');
        });
    }
};
