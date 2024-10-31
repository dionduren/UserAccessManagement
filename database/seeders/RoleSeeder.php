<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Use firstOrCreate to avoid duplicate entries
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $reviewer = Role::firstOrCreate(['name' => 'Reviewer']);

        // Define permissions and use firstOrCreate for them as well
        $permissions = [
            'manage company info',
            'manage roles',
            'manage access-matrix',
            'manage users'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $admin->givePermissionTo($permissions);
        // $admin->givePermissionTo(['view dashboard', 'manage users', 'edit settings']);
        $manager->givePermissionTo([
            'manage roles'
        ]);
        $reviewer->givePermissionTo(['manage roles']);
    }
}
