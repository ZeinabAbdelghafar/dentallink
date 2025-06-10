<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'email' => 'testuser@example.com',
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'gender' => 'male',
        ]);
    }
}
