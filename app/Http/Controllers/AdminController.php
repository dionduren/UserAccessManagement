<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        // Check if the user has 'Admin' role
        if (Auth::user()->hasRole('Admin')) {
            return view('admin.dashboard');
        }

        abort(403, 'Unauthorized access');
    }

    public function manageUsers()
    {
        // Check if the user has 'manage users' permission
        if (Auth::user()->can('manage users')) {
            return view('admin.manage-users');
        }

        abort(403, 'Unauthorized access');
    }
}
