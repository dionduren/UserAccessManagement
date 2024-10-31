<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run()
    {
        // Create a user
        $user = User::firstOrCreate([
            'email' => 'admin@example.com'
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('password123'), // Set a secure password here
        ]);

        // Assign the 'Admin' role to this user
        $user->assignRole('Admin');

        // Define users with roles
        $users = [
            [
                'name' => 'Admin User 1',
                'email' => 'admin1@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Admin'
            ],
            [
                'name' => 'Admin User 2',
                'email' => 'admin2@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Admin'
            ],
            [
                'name' => 'Manager User 1',
                'email' => 'manager1@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Manager'
            ],
            [
                'name' => 'Manager User 2',
                'email' => 'manager2@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Manager'
            ],
            [
                'name' => 'Reviewer User 1',
                'email' => 'reviewer1@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Reviewer'
            ],
            [
                'name' => 'Reviewer User 2',
                'email' => 'reviewer2@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Reviewer'
            ],
        ];

        // Create users and assign roles
        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password']
                ]
            );
            $user->assignRole($userData['role']);
        }
    }
}
