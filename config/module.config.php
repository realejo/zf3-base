<?php

namespace Realejo;

use Realejo\View\Helper\ApplicationConfigFactory;

return [
    'realejo' => [
        'vendor' => [
            'form-validation' => [
                'css' => false,
                'js' => false
            ],
        ]
    ],
    'view_helpers' => [
        'aliases' => [
            'applicationConfig' => View\Helper\ApplicationConfig::class,
        ],
        'factories' => [
            View\Helper\ApplicationConfig::class => ApplicationConfigFactory::class
        ],
        'invokables' => [
            'formValidation' => View\Helper\FormValidation::class,
            'getInputFilter' => View\Helper\GetInputFilter::class,
        ],
    ],
];
