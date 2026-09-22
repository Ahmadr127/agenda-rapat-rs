<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminAccount extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            "name" => "Administrator",
            "username" => "admin",
            "email" => "admin@rsazra.co.id",
            "password" => bcrypt("rsazra"),
        ]);
    }
}
