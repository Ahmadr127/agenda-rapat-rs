<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User::factory()->create([
        //     "name" => "Administrator",
        //     "email" => "admin@dassa.test",
        //     "password" => bcrypt("password"),
        // ]);

        Room::create([
            "room_name" => "Training Center",
            "description" => "Ruang pelatihan utama lantai 2",
        ]);
        Room::create([
            "room_name" => "Ruang Rapat Lt. 2",
            "description" => "Ruang rapat kapasitas 20 orang",
        ]);
        Room::create([
            "room_name" => "Ruang Rapat Lt. 3",
            "description" => "Ruang rapat kapasitas 15 orang",
        ]);
        Room::create([
            "room_name" => "Auditorium",
            "description" => "Auditorium utama kapasitas 100 orang",
        ]);
        Room::create([
            "room_name" => "Ruang Direksi",
            "description" => "Ruang rapat direksi",
        ]);

        $this->call([
            BankSoalSeeder::class,
            AgendaTodaySeeder::class,
        ]);
    }
}
