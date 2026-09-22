<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminAccount extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@rsazra.co.id'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('rsazra'),
            ]
        );

        $unit = Unit::firstOrCreate(['name' => 'IT']);

        Employee::updateOrCreate(
            ['nip' => 'ADM001'],
            [
                'user_id' => $user->id,
                'full_name' => 'Administrator',
                'unit_id' => $unit->id,
                'job_position' => 'MANAGER IT',
                'structural_role' => 'Administrator',
                'profession' => 'Administrasi',
            ]
        );
    }
}
