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
            'ckeditor' => [
                'js' => false,
                'customConfig' => false,
                'ckfinder' => [
                    # needs to be defined
                    #filebrowserBrowseUrl
                    #filebrowserImageBrowseUrl
                    #filebrowserUploadUrl
                    #filebrowserImageUploadUrls
                ]
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
            'CKEditor' => View\Helper\CKEditor::class,
            'formatDate' => View\Helper\FormatDate::class,
            'text' => View\Helper\Text::class,
        ],
    ],
];
