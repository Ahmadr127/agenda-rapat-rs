<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use <Img:srcset></Img:srcset>lluminate\Models\User;

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
