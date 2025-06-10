<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Departemen;
use Illuminate\Support\Facades\Storage;

class JSONService
{
    public function generateMasterDataJson()
    {
        $data = Company::with([
            'kompartemen.departemen.jobRoles',
            'departemenWithoutKompartemen.jobRoles',
            'jobRolesWithoutRelations'
        ])->get()->map(function ($company) {
            return [
                'company_id' => $company->company_code,
                'company_name' => $company->nama,
                'kompartemen' => $company->kompartemen->map(function ($kompartemen) {
                    return [
                        'kompartemen_id' => $kompartemen->kompartemen_id,
                        'nama' => $kompartemen->nama,
                        'departemen' => $kompartemen->departemen->map(function ($departemen) {
                            return [
                                'departemen_id' => $departemen->departemen_id,
                                'nama' => $departemen->nama,
                                'job_roles' => $departemen->jobRoles->map(function ($jobRole) {
                                    return [
                                        'id' => $jobRole->id,
                                        'job_role_id' => $jobRole->job_role_id,
                                        'nama' => $jobRole->nama,
                                        'deskripsi' => $jobRole->deskripsi ?? 'N/A',
                                        'status' => $jobRole->status ?? "Deactive",
                                    ];
                                })
                            ];
                        }),
                        'job_roles' => $kompartemen->jobRoles->filter(function ($jobRole) {
                            return is_null($jobRole->departemen_id); // Exclude roles tied to departemen
                        })->map(function ($jobRole) {
                            return [
                                'id' => $jobRole->id,
                                'job_role_id' => $jobRole->job_role_id,
                                'nama' => $jobRole->nama,
                                'deskripsi' => $jobRole->deskripsi ?? 'N/A',
                                'status' => $jobRole->status ?? "Deactive",
                            ];
                        }),
                    ];
                }),
                'departemen_without_kompartemen' => $company->departemenWithoutKompartemen->map(function ($departemen) {
                    return [
                        'departemen_id' => $departemen->departemen_id,
                        'nama' => $departemen->nama,
                        'job_roles' => $departemen->jobRoles->map(function ($jobRole) {
                            return [
                                'id' => $jobRole->id,
                                'job_role_id' => $jobRole->job_role_id,
                                'nama' => $jobRole->nama,
                                'deskripsi' => $jobRole->deskripsi ?? 'N/A',
                                'status' => $jobRole->status ?? "Deactive",
                            ];
                        })
                    ];
                }),
                'job_roles_without_relations' => $company->jobRolesWithoutRelations->map(function ($jobRole) {
                    return [
                        'id' => $jobRole->id,
                        'job_role_id' => $jobRole->job_role_id,
                        'nama' => $jobRole->nama,
                        'deskripsi' => $jobRole->deskripsi ?? 'N/A',
                        'status' => $jobRole->status ?? "Deactive",
                    ];
                }),
            ];
        });

        Storage::disk('public')->put('master_data.json', json_encode($data, JSON_PRETTY_PRINT));
    }
}
