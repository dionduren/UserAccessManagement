<?php

return [
    'modules' => [
        'user_nik' => [
            'name' => 'User NIK Upload',
            'table' => 'tr_user_ussm_nik',
            'user_type' => 'NIK',
            'columns' => [
                'group' => ['type' => 'lookup', 'model' => \App\Models\Company::class, 'field' => 'shortname'],
                'user_code' => ['type' => 'string'],
                'license_type' => ['type' => 'string'],
                'last_login' => ['type' => 'date'],
                'valid_from' => ['type' => 'date'],
                'valid_to' => ['type' => 'date'],
            ]
        ],

        'master_nik' => [
            'name' => 'Master Karyawan NIK Upload',
            'table' => 'ms_user_detail',
            'user_type' => null, // Optional to make controller logic consistent
            'columns' => [
                'user_code' => ['type' => 'string'],
                'nama' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'company' => [
                    'type' => 'lookup',
                    'model' => \App\Models\Company::class,
                    'field' => 'shortname',
                    'db_field' => 'company_id',
                    'id_field' => 'company_code',
                    'store_raw' => true,
                ],
                'direktorat' => ['type' => 'string'],
                'kompartemen' => [
                    'type' => 'lookup',
                    'model' => \App\Models\Kompartemen::class,
                    'field' => 'nama',
                    'db_field' => 'kompartemen_id',
                    'id_field' => 'kompartemen_id',
                    'store_raw' => true,
                ], // 🟢 Add this key,
                'departemen' => [
                    'type' => 'lookup',
                    'model' => \App\Models\Departemen::class,
                    'field' => 'nama',
                    'db_field' => 'departemen_id',
                    'id_field' => 'departemen_id',
                    'store_raw' => true,
                ],
            ]
        ],

        'user_generic' => [
            'name' => 'User Generic Upload',
            'table' => 'tr_user_generic',
            'user_type' => 'Generic',
            'columns' => [
                'group' => ['type' => 'string'],
                'user_code' => ['type' => 'string'],
                'cc_code' => [
                    'type' => 'string',
                    'field' => 'cost_code'
                ],
                'license_type' => ['type' => 'string'],
                'last_login' => ['type' => 'date'],
                'valid_from' => ['type' => 'date'],
                'valid_to' => ['type' => 'date'],
            ]
        ],

        'nik_job_role' => [
            'name' => 'NIK - Job Role Upload',
            'table' => 'tr_nik_job_role',
            'user_type' => null, // Optional to make controller logic consistent
            'columns' => [
                'nik' => ['type' => 'string'],
                'job_role' => [
                    'type' => 'lookup',
                    'model' => \App\Models\JobRole::class,
                    'field' => 'nama',
                    'db_field' => 'job_role_id'
                ],
            ]
        ],

        'terminated_employee' => [
            'name' => 'Terminated Employee Upload',
            'table' => 'ms_terminated_employee',
            'user_type' => null, // Optional to make controller logic consistent
            'columns' => [
                'nik' => ['type' => 'string'],
                'nama' => ['type' => 'string'],
                'tanggal_resign' => ['type' => 'date'],
                'status' => ['type' => 'string'],
                'last_login_date' => ['type' => 'date'], // Split into date
                'last_login_time' => ['type' => 'time'], // and time fields
                'valid_from' => ['type' => 'date'],
                'valid_to' => ['type' => 'date'],
            ]
        ],


    ]
];
