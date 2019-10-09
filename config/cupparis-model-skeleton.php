<?php

return [

    'layout-view' => "layouts/cupparijs",

    'default-models-dir' => 'Models',

    'models_namespace' => '//App//Models//',

    'modelsconf' => [
        'templatePathJs' => '/js/',
        'single' => false,
        'singleModelsConfsFile' => 'ModelsConfs.js',
        'subModelsConfsPath' => 'ModelsConfs/',
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
    ],

    'config_models_file' => 'models.php',
    'config_models_file_routes_entry' => ['route_models','modelsconfs'],
];