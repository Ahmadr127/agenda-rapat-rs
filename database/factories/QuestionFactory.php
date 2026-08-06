<?php

namespace Database\Factories;

use App\Models\BankSoal;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'bank_soal_id' => BankSoal::factory(),
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
