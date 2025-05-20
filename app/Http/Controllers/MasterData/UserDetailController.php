<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;

use App\Models\UserDetail;
use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('master-data.user_detail.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getData()
    {
        // Cache the data for 5 minutes
        return Cache::remember('user_details_data', 300, function () {
            return UserDetail::with([
                'company_data:company_code,nama',
                'kompartemen:kompartemen_id,nama',
                'departemen:departemen_id,nama'
            ])
                ->select(
                    'id',
                    'nama',
                    'nik',
                    'company_id',
                    'direktorat',
                    'kompartemen_id',
                    'departemen_id',
                    'email',
                    'created_at'
                )
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'nik' => $user->nik,
                        'company' => $user->company_data?->nama,
                        'direktorat' => $user->direktorat,
                        'kompartemen' => $user->kompartemen?->nama,
                        'departemen' => $user->departemen?->nama,
                        'email' => $user->email,
                        'created_at' => $user->created_at->format('Y-m-d'),
                    ];
                });
        });
    }

    public function create()
    {
        $companies = Company::select('company_code', 'nama')->get();
        $kompartemen = Kompartemen::select('kompartemen_id', 'nama')->get();
        $departemen = Departemen::select('departemen_id', 'nama')->get();

        return view('master-data.user_detail.create', compact('companies', 'kompartemen', 'departemen'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|unique:ms_user_detail,nik',
            'email' => 'required|email',
            'company_id' => 'required|exists:ms_company,company_code',
            'direktorat' => 'nullable|string',
            'kompartemen_id' => 'nullable|exists:ms_kompartemen,kompartemen_id',
            'departemen_id' => 'nullable|exists:ms_departemen,departemen_id',
        ]);

        $validated['created_by'] = auth()->user()->name;
        UserDetail::create($validated);
        Cache::forget('user_details_data');

        return response()->json(['success' => true]);
    }

    public function show(UserDetail $userDetail)
    {
        $userDetail->load(['company_data', 'kompartemen', 'departemen']);
        return response()->json($userDetail);
    }

    public function edit(UserDetail $userDetail)
    {
        $companies = Company::select('company_code', 'nama')->get();
        $kompartemen = Kompartemen::select('kompartemen_id', 'nama')->get();
        $departemen = Departemen::select('departemen_id', 'nama')->get();

        return view(
            'master-data.user_detail.edit',
            compact('userDetail', 'companies', 'kompartemen', 'departemen')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserDetail $userDetail)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|unique:ms_user_detail,nik,' . $userDetail->id,
            'email' => 'required|email',
            'company_id' => 'required|exists:ms_company,company_code',
            'direktorat' => 'nullable|string',
            'kompartemen_id' => 'nullable|exists:ms_kompartemen,kompartemen_id',
            'departemen_id' => 'nullable|exists:ms_departemen,departemen_id',
        ]);

        $validated['updated_by'] = auth()->user()->name;
        $userDetail->update($validated);

        // Clear the cache to reflect changes
        Cache::forget('user_details_data');

        return response()->json([
            'success' => true,
            'message' => 'User detail updated successfully'
        ]);
    }

    /**
     * Soft delete the specified resource from storage.
     */
    public function destroy(UserDetail $userDetail)
    {
        try {
            $userDetail->deleted_by = auth()->user()->name;
            $userDetail->save();
            $userDetail->delete(); // Soft delete

            // Clear the cache to reflect changes
            Cache::forget('user_details_data');

            return response()->json([
                'success' => true,
                'message' => 'User detail deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user detail'
            ], 500);
        }
    }
}
