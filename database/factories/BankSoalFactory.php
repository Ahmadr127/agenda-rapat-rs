<?php

namespace Database\Factories;

use App\Models\BankSoal;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankSoalFactory extends Factory
{
    protected $model = BankSoal::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
        ];
    }
}
