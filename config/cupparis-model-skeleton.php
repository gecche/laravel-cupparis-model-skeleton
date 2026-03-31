<?php

return [

    'namespace' => 'App\\Http\\Controllers',

    //'layout-view' => "app",
    'layout-view' => "modelskeleton::layouts/modelskeleton",

    'default-models-dir' => 'Models',

    'models_namespace' => 'App\\Models\\',
    'policies_namespace' => 'App\\Policies\\',

    'modelsconf-index' => '/vue-application-v4/src/application/ModelConfs/index.js',
    'modelsconf' => [
        'templatePathJs' => '/vue-application-v4/src/application/ModelConfs/',
        'single' => false,
        'singleModelsConfsFile' => 'ModelsConfs.js',
        'subModelsConfsPath' => '',
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


    'cupparis-app-file' => 'cupparis-app.json',

//    'config_models_list_entries' => [
//        'json_rest' => 'models'
//    ],
    //config entries to be updated with new models
    // file => entry|entries stirng or arry of strings (dot notation)
];
