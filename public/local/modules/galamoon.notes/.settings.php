<?php

return [
    'modules' => [
        'value' => [
            'defaultNamespace' => '\\Galamoon\\Notes',
        ],
        'readonly' => true,
    ],
    'controllers' => [
        'value' => [
            'namespaces' => [
                '\\Galamoon\\Notes\\Controller' => 'api',
            ],
            'defaultNamespace' => '\\Galamoon\\Notes\\Controller',
        ],
        'readonly' => true,
    ],
];
