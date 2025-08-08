<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class AccessMatrixController extends Controller
{
    public function index()
    {
        // Retrieve all users, roles, and permissions
        $users = User::all();
        $roles = Role::all();
        $permissions = Permission::all();

        return view('access-matrix', compact('users', 'roles', 'permissions'));
    }

    // --- NEW: Assign Roles page + data
    public function rolesIndex()
    {
        $roles = Role::orderBy('name')->get(['id', 'name']);
        return view('admin.access-matrix.roles', compact('roles'));
    }

    public function rolesData(Request $request)
    {
        $roles = Role::orderBy('name')->pluck('name')->all();

        $query = User::query()->select(['id', 'name', 'email'])->with('roles:id,name');

        $dt = DataTables::eloquent($query);

        foreach ($roles as $roleName) {
            $key = 'role_' . Str::slug($roleName, '_');
            $dt->addColumn($key, function (User $u) use ($roleName) {
                $checked = $u->roles->contains('name', $roleName) ? 'checked' : '';
                $roleAttr = e($roleName);
                return '<div class="form-check form-switch m-0">
                            <input type="checkbox" class="form-check-input role-toggle"
                                   data-user="' . $u->id . '" data-role="' . $roleAttr . '" ' . $checked . '>
                        </div>';
            });
        }

        return $dt->rawColumns(array_map(fn($r) => 'role_' . Str::slug($r, '_'), $roles))->toJson();
    }

    // --- NEW: Assign Permissions page + data
    public function permissionsIndex()
    {
        $permissions = Permission::orderBy('name')->get(['id', 'name']);
        return view('admin.access-matrix.permissions', compact('permissions'));
    }

    public function permissionsData(Request $request)
    {
        $perms = Permission::orderBy('name')->pluck('name')->all();

        $query = Role::query()->select(['id', 'name'])->with('permissions:id,name');

        $dt = DataTables::eloquent($query);

        foreach ($perms as $permName) {
            $key = 'perm_' . Str::slug($permName, '_');
            $dt->addColumn($key, function (Role $r) use ($permName) {
                $checked = $r->permissions->contains('name', $permName) ? 'checked' : '';
                $permAttr = e($permName);
                return '<div class="form-check form-switch m-0">
                            <input type="checkbox" class="form-check-input perm-toggle"
                                   data-role="' . $r->name . '" data-permission="' . $permAttr . '" ' . $checked . '>
                        </div>';
            });
        }

        return $dt->rawColumns(array_map(fn($p) => 'perm_' . Str::slug($p, '_'), $perms))->toJson();
    }

    public function assignRole(Request $request)
    {
        $user = User::find($request->user_id);
        $roleName = $request->role_name;

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.']);
        }

        if ($request->assign) {
            // Attempt to assign role
            $user->assignRole($roleName);
            return response()->json(['status' => 'success', 'message' => "Role '{$roleName}' assigned to {$user->name} successfully."]);
        } else {
            // Attempt to remove role
            $user->removeRole($roleName);
            return response()->json(['status' => 'warning', 'message' => "Role '{$roleName}' removed from {$user->name} successfully."]);
        }
    }

    public function assignPermission(Request $request)
    {
        $role = Role::where('name', $request->role_name)->first();
        $permissionName = $request->permission_name;

        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role not found.']);
        }

        if ($request->assign) {
            // Attempt to assign permission
            $role->givePermissionTo($permissionName);
            return response()->json(['status' => 'success', 'message' => "Permission '{$permissionName}' assigned to role '{$role->name}' successfully."]);
        } else {
            // Attempt to revoke permission
            $role->revokePermissionTo($permissionName);
            return response()->json(['status' => 'warning', 'message' => "Permission '{$permissionName}' revoked from role '{$role->name}' successfully."]);
        }
    }
}
