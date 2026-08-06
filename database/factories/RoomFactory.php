<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'room_name' => $this->faker->randomElement(['Training Center', 'Ruang Rapat Lt. 2', 'Ruang Rapat Lt. 3', 'Auditorium', 'Ruang Direksi']),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
