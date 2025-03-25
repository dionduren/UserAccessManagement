<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\TerminatedEmployee;
use Illuminate\Http\Request;

class TerminatedEmployeeController extends Controller
{
    public function index()
    {
        return view('master-data.terminated_employee.index');
    }

    public function create()
    {
        return view('master-data.terminated_employee.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string',
            'nama' => 'required|string',
            'tanggal_resign' => 'nullable|date',
            'status' => 'nullable|string',
            'last_login' => 'nullable|date',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date',
        ]);

        TerminatedEmployee::create($validated);
        return redirect()->route('terminated-employee.index')->with('success', 'Employee added');
    }

    public function edit(TerminatedEmployee $terminated_employee)
    {
        return view('master-data.terminated_employee.edit', compact('terminated_employee'));
    }

    public function update(Request $request, TerminatedEmployee $terminated_employee)
    {
        $validated = $request->validate([
            'nik' => 'required|string',
            'nama' => 'required|string',
            'tanggal_resign' => 'nullable|date',
            'status' => 'nullable|string',
            'last_login' => 'nullable|date',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date',
        ]);

        $terminated_employee->update($validated);
        return redirect()->route('terminated-employee.index')->with('success', 'Employee updated');
    }

    public function destroy(TerminatedEmployee $terminated_employee)
    {
        $terminated_employee->delete();
        return redirect()->route('terminated-employee.index')->with('success', 'Data deleted!');
    }


    public function getData(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search')?->value ?? '';

        $query = TerminatedEmployee::select('*');

        if ($search) {
            $query->where('nik', 'like', "%{$search}%")
                ->orWhere('nama', 'like', "%{$search}%");
        }

        $data = $query->orderBy('nik')
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => TerminatedEmployee::all()->count(),
            'recordsFiltered' => $query->count(),
            'data' => $data
        ]);
    }
}
