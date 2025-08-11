<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserLoginDetail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run()
    {
        // Define users with roles
        $users = [
            // ['name' => 'Admin User', 'email' => 'admin@example.com', 'role' => 'Super Admin'],
            ['name' => 'Super Admin', 'email' => 'superadmin@example.com', 'role' => 'Super Admin'],
            ['name' => 'Helpdesk', 'email' => 'helpdesk@example.com', 'role' => 'Helpdesk'],

            ['name' => 'A000 Admin', 'email' => 'a.admin@example.com', 'role' => 'Admin'],
            ['name' => 'A000 Editor', 'email' => 'a.editor@example.com', 'role' => 'Editor'],
            ['name' => 'A000 Viewer', 'email' => 'a.viewer@example.com', 'role' => 'Viewer'],

            ['name' => 'B000 Admin', 'email' => 'b.admin@example.com', 'role' => 'Admin'],
            ['name' => 'B000 Editor', 'email' => 'b.editor@example.com', 'role' => 'Editor'],
            ['name' => 'B000 Viewer', 'email' => 'b.viewer@example.com', 'role' => 'Viewer'],

            ['name' => 'C000 Admin', 'email' => 'c.admin@example.com', 'role' => 'Admin'],
            ['name' => 'C000 Editor', 'email' => 'c.editor@example.com', 'role' => 'Editor'],
            ['name' => 'C000 Viewer', 'email' => 'c.viewer@example.com', 'role' => 'Viewer'],

            ['name' => 'D000 Admin', 'email' => 'd.admin@example.com', 'role' => 'Admin'],
            ['name' => 'D000 Editor', 'email' => 'd.editor@example.com', 'role' => 'Editor'],
            ['name' => 'D000 Viewer', 'email' => 'd.viewer@example.com', 'role' => 'Viewer'],
        ];

        foreach ($users as $u) {
            $username = Str::before($u['email'], '@');

            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'username' => $username,
                    'password' => Hash::make('password123'),
                ]
            );

            // Ensure username is set even if user already existed
            if (!$user->username) {
                $user->username = $username;
                $user->save();
            }

            $user->syncRoles([$u['role']]);

            // after creating $user
            // Determine company_code
            if (in_array($u['email'], [
                'superadmin@example.com',
                'helpdesk@example.com',
                'a.editor@example.com',
                'a.viewer@example.com',
            ])) {
                $companyCode = 'A000';
            } elseif ($u['name'] === 'B000 Editor' || $u['name'] === 'B000 Viewer' || $u['name'] === 'B000 Admin') {
                $companyCode = 'B000';
            } elseif ($u['name'] === 'C000 Editor' || $u['name'] === 'C000 Viewer' || $u['name'] === 'C000 Admin') {
                $companyCode = 'C000';
            } elseif ($u['name'] === 'D000 Editor' || $u['name'] === 'D000 Viewer' || $u['name'] === 'D000 Admin') {
                $companyCode = 'D000';
            } else {
                $companyCode = 'A000';
            }

            UserLoginDetail::updateOrCreate(
                ['user_id' => $user->id],
                ['company_code' => $companyCode]
            );
        }
    }
}
