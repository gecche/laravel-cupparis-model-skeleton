<?php namespace Gecche\Cupparis\ModelSkeleton\Controllers;

use Gecche\Cupparis\ModelSkeleton\Services\Migration;
use Gecche\DBHelper\Facades\DBHelper;
use Illuminate\Routing\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\Config;
//use Illuminate\Support\Facades\Response;
//use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request as RequestFacade;
//use Laracasts\Flash\Flash;
use Illuminate\Support\Str;

class ModelSkeletonController extends Controller
{

    protected $skeletonConfig = null;

    /**
     * SuperuserController constructor.
     */
    public function __construct(Request $request)
    {
        ini_set('max_input_vars', 10000);

        $this->skeletonConfig = Config::get('cupparis-model-skeleton');


    }


    public function getIndex() {
        return view('modelskeleton::index');
    }

    public function getMigrations() {
        return view('modelskeleton::migrations');
    }




    public function getMigration()
    {
        return view('modelskeleton::migration');
    }

    public function postMigration()
    {

        $user = Auth::user();
        $modelDbMethods = DBHelper::helper($user->getConnection()->getName());

        $post = RequestFacade::all();

        $migration = [];

        $migration['nome_tabella'] = Arr::get($post, 'nome_tabella', '');

        $nome_campi = Arr::get($post, 'nome_campi', '');
        $nome_campi = explode(',', $nome_campi);
        $nome_campi = array_map('trim', $nome_campi);

        foreach ($nome_campi as $nome_campo) {

            $migration['campi'][$nome_campo] = [
                'nome' => $nome_campo,
            ];
        }


        $migration['options']['tipo_campi'] = [
            'string' => 'STRINGA  (lungh. <= 255)',
            'text' => 'TEXT',
            'integer' => 'INTEGER',
            'boolean' => 'BOOLEAN',
            'enum' => 'ENUM (opzioni ,)',
            'date' => 'DATE',
            'dateTime' => 'DATETIME',
            'decimal' => 'DECIMAL (totcifre,deccifre)',
            'float' => 'FLOAT',

        ];

        $migration['options']['nullable'] = [
            'si' => 'Sì',
            'no' => 'No',
        ];

        $tables = $modelDbMethods->listTables();

        $migration['options']['tables'] = ["" => " "] + $tables;


        $migration['options']['ondelete'] = [
            'cascade' => 'CASCADE',
            'set null' => 'SET NULL',
            'restrict' => 'RESTRICT',
        ];
        $migration['options']['onupdate'] = [
            'cascade' => 'CASCADE',
            'set null' => 'SET NULL',
            'restrict' => 'RESTRICT',
        ];
        $migration['options']['index'] = [
            'no' => 'Nessun indice',
            'index' => 'INDEX',
            'unique' => 'UNIQUE',
        ];
        $migration['options']['timestamps'] = [
            'no' => 'No',
            'nullable' => 'Nullable',
            'si' => 'Sì',
        ];
        $migration['options']['ownerships'] = [
            'no' => 'No',
            'nullable' => 'Nullable',
            'si' => 'Sì',
        ];

        $model = $this->_getModelInfo($migration['nome_tabella'], $migration['campi']);
        $model['options']['tables'] = ["" => " "] + $tables;

        $modelsConfs = [];
        $modelsConfs['options']['crea_modelsconfs'] = [
            'si' => 'Sì',
            'no' => 'No',
        ];

//        $modelsConfs = $this->_getModelsConfsInfo();


        return view('modelskeleton::migration2', compact(['migration', 'model', 'modelsConfs']));

    }

    public function postMigration2()
    {

        $post = RequestFacade::all();

        $migrationValues = [
            'nome_tabella' => Arr::get($post, 'nome_tabella', ''),
            'campi' => Arr::get($post, 'campi', []),
        ];

        $modelValues = $this->setModelValues($post);


        $modelsConfsValues = false;

        if (Arr::get($post, 'crea_modelsconfs', 'no') == 'si') {

            $modelsConfsValues = true;

        }


        if (!$modelsConfsValues) {

            $migrationService = new Migration($migrationValues, $modelValues, $modelsConfsValues);

            $migrationService->saveMigration();

            if ($modelValues) {
                $migrationService->saveModel();
                $migrationService->savePolicy($modelValues);
                $migrationService->saveFoorm($migrationValues,$modelValues);
            }

            //Flash::success('migrazione eseguita con successo (senza modelsconfs)');
            return view('modelskeleton::migrations', compact([]));

        } else {

            $migrationValuesJson = cupparis_json_encode($migrationValues);
            $modelValuesJson = cupparis_json_encode($modelValues);

            $modelsConfs = $this->_getModelsConfsInfo($migrationValues, $modelValues);

            $migration = $migrationValues;
            $model = $modelValues;

            return view('modelskeleton::migration3', compact([
                'migration', 'model', 'modelsConfs',
                'migrationValuesJson', 'modelValuesJson',
            ]));
        }

    }

    public function postMigration3()
    {

        $post = RequestFacade::all();

        $migrationValuesJson = Arr::get($post, 'migrationValuesJson', null);
        $migrationValues = json_decode($migrationValuesJson, true);
        $modelValuesJson = Arr::get($post, 'modelValuesJson', null);
        $modelValues = json_decode($modelValuesJson, true);

        $modelsConfsValues = $this->setModelsConfsValues($post);


        $migrationService = new Migration($migrationValues, $modelValues, $modelsConfsValues);

        $migrationService->saveMigration();

        if ($modelValues) {
            $migrationService->saveModel();
            $migrationService->savePolicy($modelValues);
            $migrationService->saveFoorm($migrationValues,$modelValues);
        }

        if ($modelsConfsValues) {
            $migrationService->saveModelConf();
        }

//        Flash::success('migrazione eseguita con successo (con modelsconfs)');
        return view('modelskeleton::index');

    }


    public function getModelconf()
    {

        $models = $this->_getModels();

        return view('modelskeleton::modelconf', compact(['models']));
    }

    public function postModelconf()
    {

        $post = RequestFacade::all();
        $modelName = Str::studly(Arr::get($post, 'nome_modello', ''));
        $fullModelName = Arr::get($this->skeletonConfig,'models_namespace','App\\') . $modelName;
        $model = new $fullModelName;


        $modelDbMethods = new ModelDBMethods($model->getConnection());


        $migrationValues['nome_tabella'] = $model->getTable();

        $modelValues = [];
        $modelValues['nome_modello'] = $modelName;

        $relations = $model->getRelationData();

        $modelValues['relation_names'] = [];
        $modelValues['relation_types'] = [];
        $modelValues['relation_models'] = [];

        foreach ($relations as $relationKey => $relationValue) {
            $modelValues['relation_names'][] = $relationKey;
            $modelValues['relation_types'][] = strtoupper(Str::snake($relationValue[0]));

            $relationModel = substr($relationValue[1], strlen(Arr::get($this->skeletonConfig,'models_namespace','App\\')) - 1);
            $modelValues['relation_models'][] = Str::snake($relationModel);
        }


        $fieldsDatatypes = $modelDbMethods->listColumnsMigrationDatatypes($migrationValues['nome_tabella']);

        foreach ($fieldsDatatypes as $fieldKey => $fieldValue) {
            if (in_array($fieldKey, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'])) {
                unset($fieldsDatatypes[$fieldKey]);
                continue;
            }

            $fieldsDatatypes[$fieldKey]['nome'] = $fieldKey;
        }

        $migrationValues['campi'] = $fieldsDatatypes;

        $migrationValuesJson = cupparis_json_encode($migrationValues);
        $modelValuesJson = cupparis_json_encode($modelValues);

        $modelsConfs = $this->_getModelsConfsInfo($migrationValues, $modelValues);

        $migration = $migrationValues;
        $model = $modelValues;

//        echo '<pre>';
//        print_r($modelsConfs);
//        echo '</pre>';

        return view('modelskeleton::modelconf2', compact([
            'migration', 'model', 'modelsConfs',
            'migrationValuesJson', 'modelValuesJson',
        ]));
    }

    public function postModelconf2()
    {

        $post = RequestFacade::all();

        $migrationValuesJson = Arr::get($post, 'migrationValuesJson', null);
        $migrationValues = json_decode($migrationValuesJson, true);
        $modelValuesJson = Arr::get($post, 'modelValuesJson', null);
        $modelValues = json_decode($modelValuesJson, true);

        $modelsConfsValues = $this->setModelsConfsValues($post);

        $migrationService = new \App\Services\Migration($migrationValues, $modelValues, $modelsConfsValues);

        $migrationService->saveModelConf();

        //Flash::success('migrazione model conf eseguita con successo');
        return view('modelskeleton::migrations', compact([]));

    }


    public function getModel()
    {

        $user = Auth::user();
        $modelDbMethods = DBHelper::helper($user->getConnection()->getName());

        $tables = $modelDbMethods->listTables();

        $model = [];

        $model['options']['tables'] = $tables;

        return view('modelskeleton::model', compact(['model']));
    }

    public function postModel()
    {

        $user = Auth::user();
        $modelDbMethods = DBHelper::helper($user->getConnection()->getName());

        $post = RequestFacade::all();
        $migration['nome_tabella'] = Arr::get($post, 'nome_tabella', '');

        $tables = $modelDbMethods->listTables();
        $fieldsDatatypes = $modelDbMethods->listColumnsMigrationDatatypes($migration['nome_tabella']);

        foreach ($fieldsDatatypes as $fieldKey => $fieldValue) {
            if (in_array($fieldKey, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'])) {
                unset($fieldsDatatypes[$fieldKey]);
                continue;
            }

            $fieldsDatatypes[$fieldKey]['nome'] = $fieldKey;
        }

        $migration['campi'] = $fieldsDatatypes;


        $model = $this->_getModelInfo($migration['nome_tabella'], $migration['campi']);
        $model['options']['tables'] = ["" => " "] + $tables;

        $modelsConfs['options']['crea_modelsconfs'] = [
            'si' => 'Sì',
            'no' => 'No',
        ];

        $migrationValuesJson = cupparis_json_encode($migration);

        return view('modelskeleton::model2', compact([
            'migration', 'model', 'modelsConfs',
            'migrationValuesJson',
        ]));
    }


    public function postModel2()
    {


        $post = RequestFacade::all();
        $migrationValuesJson = Arr::get($post, 'migrationValuesJson', null);
        $migrationValues = json_decode($migrationValuesJson, true);


        $post['crea_modello'] = 'si';
        $modelValues = $this->setModelValues($post);

        $modelsConfsValues = false;

        if (Arr::get($post, 'crea_modelsconfs', 'no') == 'si') {

            $modelsConfsValues = true;

        }


        if (!$modelsConfsValues) {

            $migrationService = new Migration($migrationValues, $modelValues, $modelsConfsValues);


            $migrationService->saveModel();
            $migrationService->savePolicy($modelValues);
            $migrationService->saveFoorm($migrationValues,$modelValues);

//            Flash::success('migrazione modello eseguita con successo (senza modelsconfs)');
            return view('modelskeleton::migrations', compact([]));

        } else {

            $migrationValuesJson = cupparis_json_encode($migrationValues);
            $modelValuesJson = cupparis_json_encode($modelValues);

            $modelsConfs = $this->_getModelsConfsInfo($migrationValues, $modelValues);

            $migation = $migrationValues;
            $model = $modelValues;

            //Flash::success('migrazione modello eseguita con successo (con modelsconfs)');
            return view('modelskeleton::model3', compact([
                'migration', 'model', 'modelsConfs',
                'migrationValuesJson', 'modelValuesJson',
            ]));
        }
    }

    public function postModel3()
    {

        $post = RequestFacade::all();

        $migrationValuesJson = Arr::get($post, 'migrationValuesJson', null);
        $migrationValues = json_decode($migrationValuesJson, true);
        $modelValuesJson = Arr::get($post, 'modelValuesJson', null);
        $modelValues = json_decode($modelValuesJson, true);

        $modelsConfsValues = $this->setModelsConfsValues($post);


        $migrationService = new Migration($migrationValues, $modelValues, $modelsConfsValues);

        $migrationService->saveModel();
        $migrationService->savePolicy($modelValues);
        $migrationService->saveFoorm($migrationValues,$modelValues);

        if ($modelsConfsValues) {
            $migrationService->saveModelConf();
        }


        //Flash::success('migrazione modello eseguita con successo (con modelsconfs)');
        return view('modelskeleton::migrations', compact([]));

    }



    protected function setModelValues($post)
    {
        $modelValues = [];
        if (Arr::get($post, 'crea_modello', 'no') == 'si') {
            $modelValues = [
                'nome_modello' => Arr::get($post, 'nome_modello', ''),
                'lang_modello_singolare' => Arr::get($post, 'lang_modello_singolare', ''),
                'lang_modello_plurale' => Arr::get($post, 'lang_modello_plurale', ''),
                'columns_for_select_list' => Arr::get($post, 'columns_for_select_list', []),
                'columns_for_autocomplete' => Arr::get($post, 'columns_for_autocomplete', []),
                'columns_for_default_order' => Arr::get($post, 'columns_for_default_order', []),
                'columns_for_default_order_direction' => Arr::get($post, 'columns_for_default_order_direction', []),
                'traits' => Arr::get($post, 'traits', []),
            ];

            $relation_names = Arr::get($post, 'relation_names', []);
            $relation_types = Arr::get($post, 'relation_types', []);
            $relation_models = Arr::get($post, 'relation_models', []);
            $relation_tables = Arr::get($post, 'relation_tables', []);
            $relation_foreignkey = Arr::get($post, 'relation_foreignkey', []);
            $relation_otherkey = Arr::get($post, 'relation_otherkey', []);
            $relation_pivotkey = Arr::get($post, 'relation_pivotkey', []);

            foreach ($relation_names as $relationKey => $relationValue) {
                if ($relationValue) {
                    continue;
                }

                unset($relation_names[$relationKey]);
                unset($relation_types[$relationKey]);
                unset($relation_models[$relationKey]);
                unset($relation_tables[$relationKey]);
                unset($relation_foreignkey[$relationKey]);
                unset($relation_otherkey[$relationKey]);
                unset($relation_pivotkey[$relationKey]);

            }

            $modelValues['relation_names'] = $relation_names;
            $modelValues['relation_types'] = $relation_types;
            $modelValues['relation_models'] = $relation_models;
            $modelValues['relation_tables'] = $relation_tables;
            $modelValues['relation_foreignkey'] = $relation_foreignkey;
            $modelValues['relation_otherkey'] = $relation_otherkey;
            $modelValues['relation_pivotkey'] = $relation_pivotkey;

        }

        return $modelValues;
    }

    protected function setModelsConfsValues($post)
    {
        $modelsConfsValues = [];


        $modelsConfsValues['nome_file_modelsconfs'] = Arr::get($post, 'nome_file_modelsconfs', '');
        $searchValues = Arr::get($post, 'modelsconfs-searchfields', []);

        $searchNomeValues = Arr::get($searchValues, 'nome', []);
        $searchOperatorValues = Arr::get($searchValues, 'operator', []);
        $searchTypeValues = Arr::get($searchValues, 'type', []);

        foreach ($searchNomeValues as $key => $value) {
            if ($value) {
                continue;
            }

            unset($searchNomeValues[$key]);
            unset($searchOperatorValues[$key]);
            unset($searchTypeValues[$key]);
        }

        $modelsConfsValues['search']['nome'] = $searchNomeValues;
        $modelsConfsValues['search']['operator'] = $searchOperatorValues;
        $modelsConfsValues['search']['type'] = $searchTypeValues;


        $listValues = Arr::get($post, 'modelsconfs-listfields', []);

        $listNomeValues = Arr::get($listValues, 'nome', []);
        $listTypeValues = Arr::get($listValues, 'type', []);
        $listOrderValues = Arr::get($listValues, 'order', []);

        foreach ($listNomeValues as $key => $value) {
            if ($value) {
                continue;
            }

            unset($listNomeValues[$key]);
            unset($listTypeValues[$key]);
            unset($listOrderValues[$key]);
        }

        $modelsConfsValues['list']['nome'] = $listNomeValues;
        $modelsConfsValues['list']['order'] = $listOrderValues;
        $modelsConfsValues['list']['type'] = $listTypeValues;

        $editValues = Arr::get($post, 'modelsconfs-editfields', []);

        $editNomeValues = Arr::get($editValues, 'nome', []);
        $editTypeValues = Arr::get($editValues, 'type', []);

        foreach ($editNomeValues as $key => $value) {
            if ($value) {
                continue;
            }

            unset($editNomeValues[$key]);
            unset($editTypeValues[$key]);
        }

        $modelsConfsValues['edit']['nome'] = $editNomeValues;
        $modelsConfsValues['edit']['type'] = $editTypeValues;

        return $modelsConfsValues;
    }


    protected function _getModelInfo($nomeTabella, $campi)
    {

        $model = [];

        $model['name'] = Str::studly($nomeTabella);
        $campi = array_combine(array_keys($campi), array_keys($campi));
        $model['options']['campi'] = ['no' => ''] + $campi;

        $model['options']['crea_modello'] = [
            'si' => 'Sì',
            'no' => 'No',
        ];
        $model['options']['order'] = [
            'ASC' => 'ASC',
            'DESC' => 'DESC',
        ];


        $model['options']['relations-types'] = [
            'BELONGS_TO' => 'BELONGS TO',
            'BELONGS_TO_MANY' => 'BELONGS TO MANY',
            'HAS_MANY' => 'HAS MANY',
        ];

        $modelsAndTraits = $this->getModelAndTraitNames();

        $models = array_map(['Illuminate\Support\Str','studly'], $modelsAndTraits['models']);
        $traits = array_map(['Illuminate\Support\Str','studly'], $modelsAndTraits['traits']);

        $model['options']['relations-models'] = array_combine($models, $models);
        $model['options']['traits'] = array_combine($traits, $traits);

        return $model;
    }

    protected function _getModelsConfsFile($modelValues = []) {

        $modelsConfsParams = Arr::get($this->skeletonConfig,'modelsconf',[]);



        if ($modelsConfsParams['single']) {
            return $modelsConfsParams['templatePathJs'] . $modelsConfsParams['singleModelsConfsFile'];
        }

        $files = new Filesystem();

        $relativePath = $modelsConfsParams['templatePathJs'] . $modelsConfsParams['subModelsConfsPath'];
        $fullPath = public_path($relativePath);

        $fullPathTrimmed = rtrim($fullPath,"/");
        if (!$files->isDirectory($fullPathTrimmed)) {
            $files->makeDirectory(rtrim($fullPath,"/"),0755,true);
        }

        $nomeModello = Str::studly(Arr::get($modelValues,'nome_modello',''));

        return $relativePath . 'M_'.$nomeModello . '.js';

    }

    protected function _getModelsConfsInfo($migrationValues = [], $modelValues = [])
    {
        $modelsConfs = [];
        $modelsConfs['nome_file_modelsconfs'] = $this->_getModelsConfsFile($modelValues);
        $modelsConfs['options']['crea_modelsconfs'] = [
            'si' => 'Sì',
            'no' => 'No',
        ];
        $modelsConfs['options']['search_operator'] = [
            'no' => ' ',
            '=' => '=',
            'like' => 'like',
        ];
        $modelsConfs['options']['search_types'] = [
            'no' => ' ',
            'autocomplete' => 'autocomplete',
            'between_date' => 'between_date',
            'choice-checkbox' => 'choice-checkbox',
            'choice-radio' => 'choice-radio',
            'date-picker' => 'date-picker',
            'date-select' => 'date-select',
            'input' => 'input',
            'input-hidden' => 'input-hidden',
            'input-number' => 'input-number',
            'select' => 'select',
            'text' => 'text',
        ];
        $modelsConfs['options']['list_types'] = [
            'no' => ' ',
            'autocomplete' => 'autocomplete',
            'belongsto' => 'belongsto',
            'between_date' => 'between_date',
            'choice-checkbox' => 'choice-checkbox',
            'choice-radio' => 'choice-radio',
            'date-picker' => 'date-picker',
            'date-select' => 'date-select',
            'decimal' => 'decimal',
            'hasmany' => 'hasmany',
            'hasmany_through' => 'hasmany_through',
            'image' => 'image',
            'input' => 'input',
            'input-hidden' => 'input-hidden',
            'input-number' => 'input-number',
            'input-password' => 'input-password',
            'select' => 'select',
            'swap' => 'swap',
            'text' => 'text',
            'textarea' => 'textarea',
            'texthtml' => 'texthtml',
        ];
        $modelsConfs['options']['list_orders'] = [
            'no' => 'No',
            'si' => 'Sì',
        ];
        $modelsConfs['options']['edit_types'] = [
            'no' => ' ',
            'autocomplete' => 'autocomplete',
            'between_date' => 'between_date',
            'choice-checkbox' => 'choice-checkbox',
            'choice-radio' => 'choice-radio',
            'date-picker' => 'date-picker',
            'date-select' => 'date-select',
            'decimal' => 'decimal',
            'hasmany' => 'hasmany',
            'hasmany_through' => 'hasmany_through',
            'image' => 'image',
            'input' => 'input',
            'input-hidden' => 'input-hidden',
            'input-number' => 'input-number',
            'input-password' => 'input-password',
            'select' => 'select',
            'text' => 'text',
            'textarea' => 'textarea',
            'texthtml' => 'texthtml',
        ];

        $modelsConfs['campi'] = [];

        foreach (Arr::get($migrationValues, 'campi', []) as $fieldKey => $fieldValue) {

            if (in_array($fieldKey, ['timestamps', 'ownerships'])) {
                continue;
            }

            $type = Arr::get($fieldValue, 'tipo', Arr::get($fieldValue, 'type'));
            $modelsConfs['campi'][$fieldKey] = [
                'type' => $type,
                'defaultConf' => $this->_getDefaultFieldConf($fieldKey,$type),
            ];

        }

        $modelsConfs['relazioni'] = [];

        foreach ($modelValues['relation_names'] as $relationKey => $relationName) {

            $type = $modelValues['relation_types'][$relationKey];
            $modelsConfs['relazioni'][$relationName] = [
                'type' => $type,
                'model' => $modelValues['relation_models'][$relationKey],
                'defaultConf' => $this->_getDefaultFieldConf($fieldKey,$type),
            ];

        }


        return $modelsConfs;
    }

    public function _getDefaultFieldConf($fieldKey,$type)
    {

        switch ($type) {
            case 'string':// => 'STRINGA  (lungh. <= 255)',
                $defaultConf = [
                    'search' => [
                        'type' => 'input',
                        'operator' => 'like',
                    ],
                    'list' => [
                        'type' => 'text',
                        'order' => 'si',
                    ],
                    'edit' => [
                        'type' => 'input',
                    ],
                ];
                break;
            case 'text':// => 'TEXT',
                $defaultConf = [
                    'search' => [
                        'type' => 'no',
                        'operator' => 'like',
                    ],
                    'list' => [
                        'type' => 'no',
                        'order' => 'no',
                    ],
                    'edit' => [
                        'type' => 'texthtml',
                    ],
                ];
                break;
            case 'integer':// => 'INTEGER',
                if (Str::endsWith($fieldKey,'_id')) {
                    $defaultConf = [
                        'search' => [
                            'type' => 'select',
                            'operator' => '=',
                        ],
                        'list' => [
                            'type' => 'no',
                            'order' => 'no',
                        ],
                        'edit' => [
                            'type' => 'select',
                        ],
                    ];

                } else {

                    $defaultConf = [
                        'search' => [
                            'type' => 'input-number',
                            'operator' => '=',
                        ],
                        'list' => [
                            'type' => 'text',
                            'order' => 'si',
                        ],
                        'edit' => [
                            'type' => 'input-number',
                        ],
                    ];
                }
                break;
            case 'boolean':// => 'BOOLEAN',
                $defaultConf = [
                    'search' => [
                        'type' => 'select',
                        'operator' => '=',
                    ],
                    'list' => [
                        'type' => 'swap',
                        'order' => 'si',
                    ],
                    'edit' => [
                        'type' => 'choice-radio',
                    ],
                ];
                break;
            case 'enum':// => 'ENUM (opzioni ,)',
                $defaultConf = [
                    'search' => [
                        'type' => 'select',
                        'operator' => '=',
                    ],
                    'list' => [
                        'type' => 'text',
                        'order' => 'si',
                    ],
                    'edit' => [
                        'type' => 'select',
                    ],
                ];
                break;
            case 'date':// => 'DATE',
            case 'dateTime':// => 'DATETIME',
                $defaultConf = [
                    'search' => [
                        'type' => 'no',
                        'operator' => '=',
                    ],
                    'list' => [
                        'type' => 'text',
                        'order' => 'si',
                    ],
                    'edit' => [
                        'type' => 'date-select',
                    ],
                ];
                break;

            case 'decimal':// => 'DECIMAL (totcifre,deccifre)',
            case 'float':// => 'FLOAT',
                $defaultConf = [
                    'search' => [
                        'type' => 'no',
                        'operator' => '=',
                    ],
                    'list' => [
                        'type' => 'text',
                        'order' => 'si',
                    ],
                    'edit' => [
                        'type' => 'decimal',
                    ],
                ];
                break;

            case 'BELONGS_TO':  //=> 'BELONGS TO',
                $defaultConf = [
                    'search' => [
                        'type' => 'no',
                        'operator' => '=',
                    ],
                    'list' => [
                        'type' => 'belongsto',
                        'order' => 'no',
                    ],
                    'edit' => [
                        'type' => 'no',
                    ],
                ];
                break;
            case 'BELONGS_TO_MANY':  //=> 'BELONGS TO MANY',
                $defaultConf = [
                    'search' => [
                        'type' => 'no',
                        'operator' => '=',
                    ],
                    'list' => [
                        'type' => 'hasmany_through',
                        'order' => 'no',
                    ],
                    'edit' => [
                        'type' => 'hasmany_through',
                    ],
                ];
                break;
            case 'HAS_MANY':  //=> 'HAS MANY',
                $defaultConf = [
                    'search' => [
                        'type' => 'no',
                        'operator' => '=',
                    ],
                    'list' => [
                        'type' => 'hasmany',
                        'order' => 'no',
                    ],
                    'edit' => [
                        'type' => 'hasmany',
                    ],
                ];
                break;


            default :
                $defaultConf = [
                    'search' => [
                        'type' => 'no',
                        'operator' => 'like',
                    ],
                    'list' => [
                        'type' => 'no',
                        'order' => 'si',
                    ],
                    'edit' => [
                        'type' => 'no',
                    ],
                ];
                break;

        }

        return $defaultConf;

    }

    protected function _getModels()
    {

        $files = new Filesystem();
        $filesModels = $files->allFiles(app_path() . '/Models');
        $models = [];
        foreach ($filesModels as $file) {
            if (Str::endsWith($file, '.php')) {
                $name = $file->getRelativePathName();
                $model = substr($name, 0, -4);

                if (!class_exists(Arr::get($this->skeletonConfig,'models_namespace','App\\') . $model)) {
                    continue;
                }
                if ($this->_includeModelPermissions($model)) {
                    $models[] = Str::snake($model);
                }
            }
        }

        return $models;

    }

    protected function _includeModelPermissions($model)
    {
        if (!class_exists(Arr::get($this->skeletonConfig,'models_namespace','App\\') . $model)) {
            return false;
        }

        $model = Str::snake($model);

        $suffixModelsToFilter = [
            '_attachment',
            '_video',
            '_foto',
            'test',
        ];
        if (Str::endsWith($model, $suffixModelsToFilter)) {
            return false;
        }

        $superuserModels = [
            'role',
            'user_role',
            'user_permission',
            'role_permission',
            'menu',
            'menu_item',
            'app_var',
        ];
        if (in_array($model, $superuserModels)) {
            return false;
        }

        return true;
    }

    protected function getModelAndTraitNames()
    {
        $files = new Filesystem();
        $filesModels = $files->allFiles(app_path() . '/Models');
        $models = [];
        $traits = [];
        foreach ($filesModels as $file) {
            if (Str::endsWith($file, '.php')) {
                $name = $file->getRelativePathName();
                $model = substr($name, 0, -4);

                if (class_exists(Arr::get($this->skeletonConfig,'models_namespace','App\\') . $model)) {
                    $models[] = Str::snake($model);
                }
                if (trait_exists(Arr::get($this->skeletonConfig,'models_namespace','App\\') . $model)) {
                    $traits[] = Str::snake($model);
                }
            }
        }

        return [
            'models' => $models,
            'traits' => $traits,
        ];
    }

    protected function getTraitNames()
    {

    }



}
