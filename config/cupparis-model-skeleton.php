<?php

return [

    'namespace' => 'App\\Http\\Controllers',

    'layout-view' => "layouts/modelskeleton",

    'default-models-dir' => 'Models',

    'models_namespace' => 'App\\Models\\',

    'modelsconf' => [
        'templatePathJs' => '/admin/js/',
        'single' => false,
        'singleModelsConfsFile' => 'ModelsConfs.js',
        'subModelsConfsPath' => 'ModelConfs/',
    ],

    'langs' => [
        'model' => [
            'lang/it/model.php',
        ],
        'fields' => [
            'lang/it/fields.php',
        ],
    ],

    'stubs' => [
        'migration' => 'stubs/migration/migration.stub',
        'model' => 'stubs/migration/model.stub',
        'modelconf' => 'stubs/migration/modelconf.stub',
        'config' => 'stubs/migration/config.stub',
        'fieldsTypesPath' => 'stubs/migration/modelsConfsFieldsTypes/',
        'policy' => 'stubs/migration/policy.stub',
        'foorm' => 'stubs/migration/foorm.stub',
    ],

    'config_models_list_entries' => ['json_rest' => 'models'], //config entries to be updated with new models
    // file => entry|entries stirng or arry of strings (dot notation)
];
