<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
            return response()->json(['status' => 'success', 'message' => "Role '{$roleName}' removed from {$user->name} successfully."]);
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
            return response()->json(['status' => 'success', 'message' => "Permission '{$permissionName}' revoked from role '{$role->name}' successfully."]);
        }
    }
}
