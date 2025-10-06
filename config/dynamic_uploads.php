<?php

use App\Models\UserDetail;
use App\Models\MasterDataKaryawanLocal;
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
            'name' => 'Master Karyawan NIK',
            'user_type' => null,
            'table' => 'ms_user_detail',
            'model' => UserDetail::class,
            'uses_periode' => true,
            'where_fields' => ['nik'],
            'columns' => [
                'company' => ['type' => 'lookup', 'header_name' => 'Perusahaan', 'model' => Company::class, 'id_field' => 'shortname', 'db_field' => 'company_id', 'alias' => 'company_id'],
                'nik' => ['type' => 'string', 'header_name' => 'NIK'],
                'nama' => ['type' => 'string', 'header_name' => 'Nama User'],
                'direktorat' => ['type' => 'string', 'header_name' => 'Direktorat'],
                'kompartemen_id' => ['type' => 'lookup', 'model' => Kompartemen::class, 'id_field' => 'kompartemen_id', 'header_name' => 'ID Kompartemen'],
                'departemen_id' => ['type' => 'lookup', 'model' => Departemen::class, 'id_field' => 'departemen_id', 'header_name' => 'ID Departemen'],
            ],
            'validate_columns' => ['kompartemen_id', 'departemen_id']
        ],
        'user_nik' => [
            'name' => 'User ID NIK',
            'user_type' => 'NIK',
            'table' => 'tr_user_ussm_nik',
            'model' => userNIK::class,
            'uses_periode' => true,
            'where_fields' => ['user_code'],
            'columns' => [
                'user_code' => ['type' => 'string'],
                'user_type' => ['type' => 'string'],
                'group' => ['type' => 'string'],
                'license_type' => ['type' => 'string'],
                'valid_from' => ['type' => 'date'],
                'valid_to' => ['type' => 'date'],
            ],
        ],
        // 'user_generic' => [
        //     'name' => 'User Generic Upload',
        //     'user_type' => 'Generic',
        //     'table' => 'tr_user_generic',
        //     'model' => userGeneric::class,
        //     'uses_periode' => true,
        //     'where_fields' => ['user_code'],
        //     'columns' => [
        //         'user_code' => ['type' => 'string'],
        //         'user_type' => ['type' => 'string'],
        //         'group' => ['type' => 'string'],
        //         'cost_code' => ['type' => 'string'],
        //         'license_type' => ['type' => 'string'],
        //         'valid_from' => ['type' => 'date'],
        //         'valid_to' => ['type' => 'date'],
        //     ],
        // ],
        'nik_job_role' => [
            'name' => 'USSM - Job Role',
            'user_type' => null, // Optional to make controller logic consistent
            'table' => 'tr_ussm_job_role',
            'model' => NIKJobRole::class,
            'uses_periode' => true,
            'where_fields' => ['nik'],
            'columns' => [
                // 'nik' => ['type' => 'string', 'is_nik' => true],
                'nik' => ['type' => 'lookup', 'alias' => 'nik', 'model' => MasterDataKaryawanLocal::class, 'id_field' => 'nik'],
                // 'job_role_id' => ['type' => 'lookup', 'model' => JobRole::class, 'id_field' => 'job_role_id'],
                // 'job_role' => ['type' => 'string'],
                'job_role' => ['type' => 'lookup', 'header_name' => 'Job Role ID', 'model' => JobRole::class, 'id_field' => 'nama',  'db_field' => 'job_role_id'],
            ],
        ],
        // 'terminated_employee' => [
        //     'name' => 'Terminated Employee Upload',
        //     'table' => 'ms_terminated_employee',
        //     'model' => TerminatedEmployee::class,
        //     'uses_periode' => true,
        //     'where_fields' => ['nik'],
        //     'columns' => [
        //         'nik' => ['type' => 'string', 'is_nik' => true],
        //         'nama' => ['type' => 'string'],
        //         'tanggal_resign' => ['type' => 'date'],
        //         'status' => ['type' => 'string'],
        //         'last_login' => [
        //             'type' => 'datetime',
        //             'header_name' => 'Last Login', // Custom header name
        //             'format' => 'Y-m-d H:i:s'  // Display format
        //         ],
        //         'valid_from' => ['type' => 'date'],
        //         'valid_to' => ['type' => 'date'],
        //     ],
        //     'composite_datetime' => [
        //         'last_login' => ['last_login_date', 'last_login_time'],
        //     ],
        // ],
        // 'current_cc_user' => [
        //     'name' => 'Current Cost Center User',
        //     'table' => 'ms_cc_user',
        //     'model' => CostCurrentUser::class,
        //     'uses_periode' => true,
        //     'where_fields' => ['user_code'],
        //     'columns' => [
        //         'user_code' => ['type' => 'string'],
        //         'user_name' => ['type' => 'string'],
        //         'cost_code' => ['type' => 'string'],
        //     ],
        // ],
        // 'prev_cc_user' => [
        //     'name' => 'Previous Cost Center User',
        //     'table' => 'tr_cc_prev_user',
        //     'model' => CostPrevUser::class,
        //     'uses_periode' => true,
        //     'where_fields' => ['user_code'],
        //     'columns' => [
        //         'user_code' => ['type' => 'string'],
        //         'user_name' => ['type' => 'string'],
        //         'cost_code' => ['type' => 'string'],
        //     ],
        // ],
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
