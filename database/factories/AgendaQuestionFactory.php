<?php

namespace Database\Factories;

use App\Models\Agenda;
use App\Models\AgendaQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgendaQuestionFactory extends Factory
{
    protected $model = AgendaQuestion::class;

    public function definition(): array
    {
        return [
            'agenda_id' => Agenda::factory(),
            'question_text' => fake()->sentence() . '?',
            'option_a' => fake()->sentence(2),
            'option_b' => fake()->sentence(2),
            'option_c' => fake()->sentence(2),
            'option_d' => fake()->sentence(2),
            'option_e' => fake()->sentence(2),
            'correct_option' => fake()->randomElement(['a', 'b', 'c', 'd', 'e']),
        ];
    }
}
