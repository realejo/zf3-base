<?php

namespace Realejo;

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
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory'
        ],
        'factories' => [
            Cache\CacheService::class => Cache\CacheFactory::class,
            Backup\BackupService::class => Backup\BackupFactory::class
        ],
    ],
    'view_helpers' => [
        'aliases' => [
            'applicationConfig' => View\Helper\ApplicationConfig::class,
        ],
        'factories' => [
            View\Helper\ApplicationConfig::class => View\Helper\ApplicationConfigFactory::class
        ],
        'invokables' => [
            'formValidation' => View\Helper\FormValidation::class,
            'getInputFilter' => View\Helper\GetInputFilter::class,
            'CKEditor' => View\Helper\CKEditor::class,
            'formatFileSize' => View\Helper\FormatFileSize::class,
            'formatDate' => View\Helper\FormatDate::class,
            'text' => View\Helper\Text::class,
            'frmEnumChecked' => View\Helper\FrmEnumChecked::class,
            'frmEnumCheckbox' => View\Helper\FrmEnumCheckbox::class,
            'frmEnumSelect' => View\Helper\FrmEnumSelect::class,
        ],
    ],
];
