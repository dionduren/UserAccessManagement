<?php

use App\Models\UserDetail;
use App\Models\userNIK;
use App\Models\userGeneric;
use App\Models\NIKJobRole;
use App\Models\TerminatedEmployee;
use App\Models\CostCurrentUser;
use App\Models\CostPrevUser;
use App\Models\CostCenter;
use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobRole;

return [
    'modules' => [
        'master_nik' => [
            'name' => 'Master Karyawan NIK Upload',
            'user_type' => null,
            'table' => 'ms_user_detail',
            'model' => UserDetail::class,
            'where_fields' => ['nik', 'periode_id'],
            'columns' => [
                'company' => ['type' => 'string'],
                'nik' => ['type' => 'string'],
                'nama' => ['type' => 'string'],
                'direktorat' => ['type' => 'string'],
                'kompartemen_id' => ['type' => 'lookup', 'model' => Kompartemen::class, 'id_field' => 'kompartemen_id'],
                'departemen_id' => ['type' => 'lookup', 'model' => Departemen::class, 'id_field' => 'departemen_id'],
                'periode_id' => ['type' => 'string'], // hidden from table but kept in payload
            ],
            'validate_columns' => ['kompartemen_id', 'departemen_id']
        ],
        'user_nik' => [
            'name' => 'User NIK Upload',
            'user_type' => 'NIK',
            'table' => 'tr_user_ussm_nik',
            'model' => userNIK::class,
            'where_fields' => ['user_code', 'periode_id'],
            'columns' => [
                'user_code' => ['type' => 'string'],
                'user_type' => ['type' => 'string'],
                'group' => ['type' => 'string'],
                'license_type' => ['type' => 'string'],
                'valid_from' => ['type' => 'date'],
                'valid_to' => ['type' => 'date'],
                'periode_id' => ['type' => 'string'],
            ],
        ],
        'user_generic' => [
            'name' => 'User Generic Upload',
            'user_type' => 'Generic',
            'table' => 'tr_user_generic',
            'model' => userGeneric::class,
            'where_fields' => ['user_code', 'periode_id'],
            'columns' => [
                'user_code' => ['type' => 'string'],
                'user_type' => ['type' => 'string'],
                'group' => ['type' => 'string'],
                'cost_code' => ['type' => 'string'],
                'license_type' => ['type' => 'string'],
                'valid_from' => ['type' => 'date'],
                'valid_to' => ['type' => 'date'],
                'periode_id' => ['type' => 'string'],
            ],
        ],
        'nik_job_role' => [
            'name' => 'NIK - Job Role Upload',
            'user_type' => null, // Optional to make controller logic consistent
            'table' => 'tr_nik_job_role',
            'model' => NIKJobRole::class,
            'where_fields' => ['nik', 'periode_id'],
            'columns' => [
                'nik' => ['type' => 'string'],
                'job_role_id' => ['type' => 'lookup', 'model' => JobRole::class],
                'periode_id' => ['type' => 'string'],
            ],
        ],
        'terminated_employee' => [
            'name' => 'Terminated Employee Upload',
            'table' => 'ms_terminated_employee',
            'model' => TerminatedEmployee::class,
            'where_fields' => ['nik'],
            'columns' => [
                'nik' => ['type' => 'string'],
                'nama' => ['type' => 'string'],
                'tanggal_resign' => ['type' => 'date'],
                'status' => ['type' => 'string'],
                'last_login' => ['type' => 'datetime'],
                'valid_from' => ['type' => 'date'],
                'valid_to' => ['type' => 'date'],
            ],
        ],
        'current_cc_user' => [
            'name' => 'Current Cost Center User',
            'table' => 'ms_cc_user',
            'model' => CostCurrentUser::class,
            'where_fields' => ['user_code', 'periode_terdaftar'],
            'columns' => [
                'user_code' => ['type' => 'string'],
                'user_name' => ['type' => 'string'],
                'cost_code' => ['type' => 'string'],
                'periode_id' => ['type' => 'string'],
            ],
        ],
        'prev_cc_user' => [
            'name' => 'Previous Cost Center User',
            'table' => 'tr_cc_prev_user',
            'model' => CostPrevUser::class,
            'where_fields' => ['user_code', 'periode_sebelumnya'],
            'columns' => [
                'user_code' => ['type' => 'string'],
                'user_name' => ['type' => 'string'],
                'cost_code' => ['type' => 'string'],
                'periode_id' => ['type' => 'string'],
            ],
        ],
        // 'ms_cost_center' => [
        //     'name' => 'Master Data Cost Center',
        //     'table' => 'ms_cost_center',
        //     'model' => CostCenter::class,
        //     'where_fields' => ['group', 'cost_code'],
        //     'columns' => [
        //         'group' => ['type' => 'string'],
        //         'cost_center' => ['type' => 'string'],
        //         'cost_code' => ['type' => 'string'],
        //         'deskripsi' => ['type' => 'string'],
        //     ],
        // ],
    ],
];
