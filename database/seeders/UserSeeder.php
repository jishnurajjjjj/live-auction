<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Date;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Date::now();
       User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_blocked' => 0,
             'created_at' => $now,
            'updated_at' => $now,
        ]);

        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'role' => 'bidder',
                'is_blocked' => 0,
                 'created_at' => $now,
               'updated_at' => $now,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password'),
                'role' => 'bidder',
                'is_blocked' => 0,
                 'created_at' => $now,
               'updated_at' => $now,
            ],
            [
                'name' => 'Bob Blocked',
                'email' => 'bob@example.com',
                'password' => Hash::make('password'),
                'role' => 'bidder',
                'is_blocked' => 1,
                 'created_at' => $now,
              'updated_at' => $now,
            ],
        ];

        User::insert($users);
    }
}
