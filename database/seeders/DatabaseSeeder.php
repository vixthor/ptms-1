<?php

namespace Database\Seeders;

use App\Models\AcademicSetting;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
        ]);

        // Many controllers (marks, attendance, academic settings) hard-depend
        // on a single settings row with id=1 existing. Nothing in the app
        // ever creates it, so a fresh install breaks Give Marks/Take
        // Attendance until this row exists.
        AcademicSetting::firstOrCreate(['id' => 1]);
    }
}
