<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->time('event_time');
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
            $table->string('organizer');
            $table->string('meeting_chair');
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->string('letter_file_path')->nullable();
            $table->string('material_file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
