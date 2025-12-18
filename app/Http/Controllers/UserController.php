<?php

namespace App\Http\Controllers;

use App\Traits\AuditsActivity;
use App\Models\User;
use App\Models\UserLoginDetail;
use App\Models\Company;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use AuditsActivity;
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::with('roles')->orderBy('id')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        $companies = Company::all();
        return view('users.manage', compact('roles', 'companies'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email'    => 'nullable|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'company_code' => 'required|string'
        ]);

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email, // optional
            'password' => Hash::make($request->password),
        ]);

        UserLoginDetail::create([
            'user_id' => $user->id,
            'company_code' => $request->company_code
        ]);

        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        // Audit trail
        $this->auditCreate($user, ['roles' => $request->roles]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $companies = Company::all();
        return view('users.manage', compact('user', 'roles', 'companies'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'company_code' => 'required|string'
        ]);

        // Store original data for audit
        $originalData = $user->toArray();

        $user->update([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
        ]);

        $user->loginDetail()->update([
            'company_code' => $request->company_code
        ]);

        if ($request->roles) {
            $user->syncRoles($request->roles);
        }

        // Audit trail
        $this->auditUpdate($user, $originalData, ['roles' => $request->roles]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from the database.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Audit trail
        $this->auditDelete($user);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
