<?php

return [
    'ui' => [
        'assets_url' => env('UXMALTECH_UI_ASSETS_URL', '/vendor/uxmaltech/backoffice-ui/dist'),
        'assets_lazy_load' => env('UXMALTECH_UI_ASSETS_LAZY_LOAD', true),
        'theme' => env('UXMAL_BACKOFFICE_UI', 'bootstrap5'),
        'layout-orientation' => env('UXMALTECH_BACKOFFICE_UI_LAYOUT_ORIENTATION', 'vertical'),
        'tidify' => env('UXMAL_BACKOFFICE_UI_TIDIFY', true),
        'logo_light' => env('UXMAL_BACKOFFICE_LOGO_LIGHT', 'public/vendor/uxmal-backoffice-ui/images/logo-light-full.png'),
        'company_name' => env('UXMAL_BACKOFFICE_COMPANY_NAME', 'Uxmal'),
        'use_adr_001' => env('UXMAL_BACKOFFICE_UI_USE_ADR_001', false),
    ],
    'cbq' => [
        'controllerClass' => Uxmal\Backend\Controllers\CBQToBrokerController::class,
        'broker' => [
            'default' => [
                'driver' => 'sync',
                'handles' => [
                    'cmd',
                    'qry',
                ],
            ]
        ]
    ]
];

