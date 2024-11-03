<?php

return [
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Task API Documentation',
            ],
            'routes' => [
                'api' => 'api/documentation',
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            'docs' => 'api/documentation/docs',
            'asset' => 'api/documentation/asset',
            'oauth2_callback' => 'api/documentation/oauth2-callback',
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
            'group_options' => []
        ],
        'paths' => [
            'docs' => storage_path('api-docs'),
            'docs_json' => 'api-docs.json',
            'docs_yaml' => 'api-docs.yaml',
            'annotations' => [
                base_path('app'),
            ],
            'views' => base_path('resources/views/vendor/l5-swagger'),
            'base' => env('L5_SWAGGER_BASE_PATH', null),
            'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),
            'excludes' => [],
        ],
        'security' => [
            'securityDefinitions' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                    ],
                ],
                'security' => [
                    [
                        'bearerAuth' => [],
                    ],
                ],
            ],
        ],
        'generate_always' => true,
        'generate_yaml' => false,
        'proxy' => false,
        'additional_config_url' => null,
        'operations_sort' => null,
        'validator_url' => null,
        'ui' => [
            'display' => [
                'doc_expansion' => 'none',
            ],
            'authorization' => [
                'persist_authorization' => true,
            ],
        ],
    ],
];