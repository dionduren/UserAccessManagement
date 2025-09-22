<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterDataKaryawanLocal;
use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class MasterDataKaryawanLocalController extends Controller
{
    public function index()
    {
        return view('master-data.karyawan_unit_kerja.index');
    }

    // Data for DataTables
    public function data(Request $request)
    {
        $query = MasterDataKaryawanLocal::query();

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $editUrl = route('karyawan_unit_kerja.edit', $row->id);
                return '
                    <a href="' . $editUrl . '" class="btn btn-sm btn-warning">Edit</a>
                    <button data-id="' . $row->id . '" class="btn btn-sm btn-danger btn-delete">Delete</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $companies = Company::orderBy('nama')
            ->get(['company_code', 'nama'])
            ->reduce(function ($acc, $c) {
                $acc[(string)$c->company_code] = $c->nama ?: $c->company_code;
                return $acc;
            }, []);

        $kompartemen = Kompartemen::orderBy('nama')
            ->get(['kompartemen_id', 'nama'])
            ->reduce(function ($acc, $k) {
                $acc[(string)$k->kompartemen_id] = $k->nama;
                return $acc;
            }, []);

        $departemen = Departemen::orderBy('nama')
            ->get(['departemen_id', 'nama'])
            ->reduce(function ($acc, $d) {
                $acc[(string)$d->departemen_id] = $d->nama;
                return $acc;
            }, []);

        return view('master-data.karyawan_unit_kerja.form', [
            'model'       => new MasterDataKaryawanLocal(),
            'companies'   => $companies,
            'kompartemen' => $kompartemen,
            'departemen'  => $departemen,
            'method'      => 'POST',
            'route'       => route('karyawan_unit_kerja.store'),
            'title'       => 'Create Master Data Karyawan',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // Auto fill name columns
        if (!empty($data['kompartemen_id'])) {
            $data['kompartemen'] = Kompartemen::where('kompartemen_id', $data['kompartemen_id'])->value('nama');
        }
        if (!empty($data['departemen_id'])) {
            $data['departemen'] = Departemen::where('departemen_id', $data['departemen_id'])->value('nama');
        }

        $data['created_by'] = Auth::user()?->name ?? 'system';
        $data['updated_by'] = $data['created_by'];

        MasterDataKaryawanLocal::create($data);

        return redirect()->route('karyawan_unit_kerja.index')->with('success', 'Created.');
    }

    public function edit($id)
    {
        $model = MasterDataKaryawanLocal::findOrFail($id);

        $companies = Company::orderBy('nama')
            ->get(['company_code', 'nama'])
            ->reduce(function ($acc, $c) {
                $acc[(string)$c->company_code] = $c->nama ?: $c->company_code;
                return $acc;
            }, []);

        $kompartemen = Kompartemen::orderBy('nama')
            ->get(['kompartemen_id', 'nama'])
            ->reduce(function ($acc, $k) {
                $acc[(string)$k->kompartemen_id] = $k->nama;
                return $acc;
            }, []);

        $departemen = Departemen::orderBy('nama')
            ->get(['departemen_id', 'nama'])
            ->reduce(function ($acc, $d) {
                $acc[(string)$d->departemen_id] = $d->nama;
                return $acc;
            }, []);

        return view('master-data.karyawan_unit_kerja.form', [
            'model'       => $model,
            'companies'   => $companies,
            'kompartemen' => $kompartemen,
            'departemen'  => $departemen,
            'method'      => 'PUT',
            'route'       => route('karyawan_unit_kerja.update', $model->id),
            'title'       => 'Edit Master Data Karyawan',
        ]);
    }

    public function update(Request $request, $id)
    {
        $model = MasterDataKaryawanLocal::findOrFail($id);
        $data  = $this->validateData($request);

        if (!empty($data['kompartemen_id'])) {
            $data['kompartemen'] = Kompartemen::where('kompartemen_id', $data['kompartemen_id'])->value('nama');
        } else {
            $data['kompartemen'] = null;
        }

        if (!empty($data['departemen_id'])) {
            $data['departemen'] = Departemen::where('departemen_id', $data['departemen_id'])->value('nama');
        } else {
            $data['departemen'] = null;
        }

        $data['updated_by'] = Auth::user()?->name ?? 'system';
        $model->update($data);

        return redirect()->route('karyawan_unit_kerja.index')->with('success', 'Updated.');
    }

    public function destroy($id)
    {
        $model = MasterDataKaryawanLocal::findOrFail($id);
        $model->delete();
        return response()->json(['status' => 'ok']);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nik'            => 'required|string|max:50',
            'nama'           => 'required|string|max:150',
            'company'        => 'required|string|max:20',          // company_code
            'direktorat_id'  => 'nullable|string|max:50',
            'direktorat'     => 'nullable|string|max:150',
            'kompartemen_id' => 'nullable|string|max:50',
            'departemen_id'  => 'nullable|string|max:50',
            'atasan'         => 'nullable|string|max:150',
            'cost_center'    => 'nullable|string|max:50',
        ]);
    }
}
