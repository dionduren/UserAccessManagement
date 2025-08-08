<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Define permissions
        $permissions = [
            // Data + reports
            'data.view',
            'data.create',
            'data.update',
            'data.delete',
            'report.generate',

            // Admin/control
            'manage users',
            'manage roles',
            'manage access-matrix',
            'manage company info',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Global roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $helpdesk   = Role::firstOrCreate(['name' => 'Helpdesk']);

        // Super Admin: all permissions
        $superAdmin->syncPermissions($permissions);

        // Helpdesk: everything except managing users
        $helpdesk->syncPermissions(array_diff($permissions, ['manage users']));

        // Company-specific roles
        $companies = ['A000', 'B000', 'C000', 'D000'];
        $editorPerms = ['data.view', 'data.create', 'data.update', 'data.delete', 'report.generate'];
        $viewerPerms = ['data.view', 'report.generate'];

        foreach ($companies as $code) {
            $editor  = Role::firstOrCreate(['name' => "{$code} Editor"]);
            $viewer  = Role::firstOrCreate(['name' => "{$code} Viewer"]);
            $editor->syncPermissions($editorPerms);
            $viewer->syncPermissions($viewerPerms);
        }
    }
}
