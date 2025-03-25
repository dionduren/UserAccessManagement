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
            'name' => 'Master Data Detail Upload',
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
                    'db_field' => 'company_id', // 🟢 Add this key
                ],
                'direktorat' => ['type' => 'string'],
                'kompartemen' => [
                    'type' => 'lookup',
                    'model' => \App\Models\Kompartemen::class,
                    'field' => 'name',
                    'db_field' => 'kompartemen_id'
                ], // 🟢 Add this key,
                'departemen' => [
                    'type' => 'lookup',
                    'model' => \App\Models\Departemen::class,
                    'field' => 'name',
                    'db_field' => 'departemen_id'
                ],
            ]
        ],

        'user_generic' => [
            'name' => 'User Generic Upload',
            'table' => 'tr_user_generic',
            'user_type' => 'Generic',
            'columns' => [
                'group' => ['type' => 'lookup', 'model' => \App\Models\Company::class, 'field' => 'shortname'],
                'user_code' => ['type' => 'string'],
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
                    'field' => 'nama_jabatan',
                    'db_field' => 'job_role_id'
                ],
            ]
        ]
    ]
];
