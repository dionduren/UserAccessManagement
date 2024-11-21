<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Departemen;
use Illuminate\Support\Facades\Storage;

class JSONService
{
    // public function generateMasterDataJson()
    // {
    //     $data = Company::with([
    //         'kompartemen.departemen.jobRoles',
    //         'departemenWithoutKompartemen.jobRoles',
    //         'jobRolesWithoutRelations'
    //     ])->get()->map(function ($company) {
    //         return [
    //             'company_id' => $company->id,
    //             'company_name' => $company->name,
    //             'job_roles_without_relations' => $company->jobRolesWithoutRelations->map(function ($jobRole) {
    //                 return [
    //                     'id' => $jobRole->id,
    //                     'name' => $jobRole->nama_jabatan,
    //                     'description' => $jobRole->deskripsi ?? 'N/A'
    //                 ];
    //             }),
    //             'kompartemen' => $company->kompartemen->map(function ($kompartemen) {
    //                 return [
    //                     'id' => $kompartemen->id,
    //                     'name' => $kompartemen->name,
    //                     'job_roles' => $kompartemen->jobRoles
    //                         ->filter(function ($jobRole) {
    //                             return is_null($jobRole->departemen_id); // Exclude roles tied to departemen
    //                         })
    //                         ->map(function ($jobRole) {
    //                             return [
    //                                 'id' => $jobRole->id,
    //                                 'name' => $jobRole->nama_jabatan,
    //                                 'description' => $jobRole->deskripsi ?? 'N/A'
    //                             ];
    //                         }),
    //                     'departemen' => $kompartemen->departemen->map(function ($departemen) {
    //                         return [
    //                             'id' => $departemen->id,
    //                             'name' => $departemen->name,
    //                             'job_roles' => $departemen->jobRoles->map(function ($jobRole) {
    //                                 return [
    //                                     'id' => $jobRole->id,
    //                                     'name' => $jobRole->nama_jabatan,
    //                                     'description' => $jobRole->deskripsi ?? 'N/A'
    //                                 ];
    //                             })
    //                         ];
    //                     })
    //                 ];
    //             }),
    //             'departemen_without_kompartemen' => $company->departemenWithoutKompartemen->map(function ($departemen) {
    //                 return [
    //                     'id' => $departemen->id,
    //                     'name' => $departemen->name,
    //                     'job_roles' => $departemen->jobRoles->map(function ($jobRole) {
    //                         return [
    //                             'id' => $jobRole->id,
    //                             'name' => $jobRole->nama_jabatan,
    //                             'description' => $jobRole->deskripsi ?? 'N/A'
    //                         ];
    //                     })
    //                 ];
    //             })
    //         ];
    //     });

    //     Storage::disk('public')->put('master_data.json', json_encode($data, JSON_PRETTY_PRINT));
    // }
    public function generateMasterDataJson()
    {
        $data = Company::with([
            'kompartemen.departemen.jobRoles',
            'departemenWithoutKompartemen.jobRoles',
            'jobRolesWithoutRelations'
        ])->get()->map(function ($company) {
            return [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'kompartemen' => $company->kompartemen->map(function ($kompartemen) {
                    return [
                        'id' => $kompartemen->id,
                        'name' => $kompartemen->name,
                        'departemen' => $kompartemen->departemen->map(function ($departemen) {
                            return [
                                'id' => $departemen->id,
                                'name' => $departemen->name,
                                'job_roles' => $departemen->jobRoles->map(function ($jobRole) {
                                    return [
                                        'id' => $jobRole->id,
                                        'name' => $jobRole->nama_jabatan,
                                        'description' => $jobRole->deskripsi ?? 'N/A',
                                    ];
                                })
                            ];
                        }),
                        'job_roles' => $kompartemen->jobRoles->filter(function ($jobRole) {
                            return is_null($jobRole->departemen_id); // Exclude roles tied to departemen
                        })->map(function ($jobRole) {
                            return [
                                'id' => $jobRole->id,
                                'name' => $jobRole->nama_jabatan,
                                'description' => $jobRole->deskripsi ?? 'N/A',
                            ];
                        }),
                    ];
                }),
                'departemen_without_kompartemen' => $company->departemenWithoutKompartemen->map(function ($departemen) {
                    return [
                        'id' => $departemen->id,
                        'name' => $departemen->name,
                        'job_roles' => $departemen->jobRoles->map(function ($jobRole) {
                            return [
                                'id' => $jobRole->id,
                                'name' => $jobRole->nama_jabatan,
                                'description' => $jobRole->deskripsi ?? 'N/A',
                            ];
                        })
                    ];
                }),
                'job_roles_without_relations' => $company->jobRolesWithoutRelations->map(function ($jobRole) {
                    return [
                        'id' => $jobRole->id,
                        'name' => $jobRole->nama_jabatan,
                        'description' => $jobRole->deskripsi ?? 'N/A',
                    ];
                }),
            ];
        });

        Storage::disk('public')->put('master_data.json', json_encode($data, JSON_PRETTY_PRINT));
    }
}
