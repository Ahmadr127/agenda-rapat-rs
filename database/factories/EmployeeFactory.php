<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'nip' => $this->faker->unique()->numerify('##################'),
            'full_name' => $this->faker->name(),
            'unit_id' => Unit::factory(),
            'job_position' => $this->faker->randomElement(['Dokter Umum', 'Perawat', 'Apoteker', 'Analis Kesehatan', 'Radiografer', 'Fisioterapis', 'Ahli Gizi', 'Staf Administrasi']),
            'structural_role' => $this->faker->randomElement(['Kepala Bagian', 'Kepala Ruangan', 'Koordinator', 'Staf', 'Wakil Kepala', 'Supervisor']),
            'profession' => $this->faker->randomElement(['Tenaga Medis', 'Tenaga Keperawatan', 'Tenaga Kefarmasian', 'Tenaga Kesehatan Lain', 'Tenaga Non-Kesehatan']),
        ];
    }
}
