<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'ICU', 'IGD', 'Rawat Inap', 'Rawat Jalan', 'Farmasi',
                'Radiologi', 'Laboratorium', 'Rehabilitasi Medik', 'Gizi',
                'Rekam Medis', 'Humas', 'Keuangan', 'SDM', 'IT',
            ]),
        ];
    }
}
