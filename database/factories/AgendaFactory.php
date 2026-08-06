<?php

namespace Database\Factories;

use App\Models\Agenda;
use App\Models\Employee;
use App\Models\Room;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgendaFactory extends Factory
{
    protected $model = Agenda::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'unit_id' => Unit::factory(),
            'event_date' => fake()->dateTimeBetween('now', '+1 month'),
            'event_time' => fake()->time('H:i'),
            'event_end_time' => fake()->optional()->time('H:i'),
            'event_leader_id' => Employee::factory(),
            'room_id' => Room::factory(),
            'type' => 'rapat',
        ];
    }
}
