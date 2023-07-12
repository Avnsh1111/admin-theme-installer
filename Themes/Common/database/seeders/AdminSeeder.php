<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'type' => 1,
            'email' => 'admin@gmail.com',
            'email_verified_at' => Carbon::now(),
            'password' => bcrypt('123456')
        ]);
    }
}
