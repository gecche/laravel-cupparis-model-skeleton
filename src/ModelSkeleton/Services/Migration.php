<?php namespace Gecche\Cupparis\ModelSkeleton\Services;

use App\Models\User;
use App\Services\Permissions;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Validator;
use Illuminate\Contracts\Auth\Registrar as RegistrarContract;

class Migration
{

    protected $migrationValues = [];
    protected $modelValues = [];
    protected $modelsConfsValues = [];

    protected $migrationPath = 'database/migrations/';

    protected $migrationTable = '';
    protected $timestamps = true;
    protected $ownerships = true;

    protected $modelName = null;
    protected $campi = [];

    protected $configModelsFile = 'config/models.php';
    protected $configModelsFileRoutesEntry = ['route_models','modelsconfs'];


//    protected $aclModels = [];


    /**
     * @var Filesystem|null
     */
    protected $files = null;

    protected $langs = null;

    protected $stubs = null;

    /**
     * Permissions constructor.
     * @param string $configFilePath
     * @param array $aclModels
     * @param array $$this->configValues
     */
    public function __construct($migrationValues = [], $modelValues = [], $modelsConfsValues = [], $files = null)
    {

        $skeletonConfig = Config::get('cupparis-model-skeleton',[]);

        $this->langs = Arr::get($skeletonConfig,'langs',[]);
        $this->stubs = Arr::get($skeletonConfig,'stubs',[]);
        $this->configModelsFile = Arr::get($skeletonConfig,'config_models_file','models.php');
        $this->configModelsFileRoutesEntry = Arr::get($skeletonConfig,'config_models_file_routes_entry',[]);


        $this->migrationValues = $migrationValues;
        $this->modelValues = $modelValues;
        $this->modelsConfsValues = $modelsConfsValues;
        $this->modelName = array_get($this->modelValues, 'nome_modello', '');


//        $this->aclModels = Config::get('acl.models');

        if (is_null($files)) {
            $this->files = new Filesystem();
        } else {
            $this->files = $files;
        }
    }

    protected function getStub($type = 'migration')
    {
        return base_path($this->stubs[$type]);
        // TODO: Implement getStub() method.
    }

    protected function getStubInPath($type = 'input',$path = 'fieldsTypesPath',$ext = 'stub')
    {
        return base_path($this->stubs[$path] . $type . '.' . $ext);
        // TODO: Implement getStub() method.
    }

    public function saveMigration()
    {

        $stub = $this->files->get($this->getStub());

        $this->migrationTable = array_get($this->migrationValues, 'nome_tabella', '');

        $stub = str_replace(
            '{{$migrationTable}}', $this->migrationTable, $stub
        );

        $migrationClass = 'Create' . studly_case($this->migrationTable)  . 'Table';

        $stub = str_replace(
            '{{$migrationClass}}', $migrationClass, $stub
        );

        $this->campi = array_get($this->migrationValues, 'campi', []);

        $migrationCampi = $this->getIndent() . '$table->increments(\'id\');' . "\n";

        foreach ($this->campi as $key => $value) {
            $migrationCampi .= $this->getIndent();


            $migrationCampi .= $this->getCampoString($key, $value);

            $migrationCampi .= "\n";


        }
        $stub = str_replace(
            '{{$migrationCampi}}', $migrationCampi, $stub
        );


        $filename = date('Y_m_d_His') . '_create_' . $this->migrationTable . '_table.php';

        $this->files->put(base_path($this->migrationPath . $filename), $stub);


    }

    protected function getIndent($n = null)
    {
        if (is_null($n)) {
            $n = 3;
        }

        $string = "";

        for ($i = 1; $i <= $n; $i++) {
            $string .= "\t";
        }

        return $string;
    }


    protected function getCampoString($fieldName, $fieldValue)
    {

        $this->timestamps = true;
        $this->ownerships = true;
        if ($fieldName == 'timestamps') {
            switch ($fieldValue) {
                case 'nullable':
                    return '$table->nullableTimestamps();';
                case 'si':
                    return '$table->timestamps();';
                default:
                    return '';
            }
        }
        if ($fieldName == 'ownerships') {
            switch ($fieldValue) {
                case 'nullable':
                    return '$table->nullableOwnerships();';
                case 'si':
                    return '$table->ownerships();';
                default:
                    return '';
            }
        }

        $fieldString = '$table->';
        $type = array_get($fieldValue, 'tipo', 'string');
        $info = array_get($fieldValue, 'info', '');
        $info = array_map('trim', explode(',', $info));
        $infoString = '';

        switch ($type) {

            case 'enum':
                foreach ($info as $option) {
                    $infoString .= "'" . $option . "',";
                }
                if ($infoString) {
                    $infoString = trim($infoString, ',');
                    $infoString = ',[' . $infoString . ']';
                } else {
                    $infoString = ',[]';
                }
                break;
            default:
                $infoString = count($info) > 0 ? ',' . implode(',', $info) : '';
                break;
        }

        $infoString = rtrim($infoString,', ');

        $fieldString .= $type . '(\'' . $fieldName . '\'' . $infoString . ')';

        switch ($type) {
            case 'integer':
                $fieldString .= '->unsigned()';
                break;
            default:
                break;
        }

        $nullable = array_get($fieldValue, 'nullable', 'no');
        if ($nullable == 'si') {
            $fieldString .= '->nullable()';
        }
        $default = array_get($fieldValue, 'default', '');
        if ($default) {
            switch ($type) {
                case 'integer':
                case 'decimal':
                case 'boolean':
                case 'float':
                    $fieldString .= '->default(' . $default . ')';
                    break;

                default:
                    $fieldString .= '->default(\'' . $default . '\')';
                    break;
            }

        }

        $index = array_get($fieldValue, 'index', '');

        $relazioneTabella = array_get($fieldValue, 'relazione_tabella', '');
        $relazioneCampo = array_get($fieldValue, 'relazione_campo', '');

        $relationString = '';
        if ($relazioneTabella && $relazioneCampo) {
            //VINCOLO A CREARE UN INDICE NON UNICO SUL CAMPO PER LA RELAZIONE
            $index = 'index';

            $onDelete = array_get($fieldValue, 'ondelete', 'restrict');
            $onUpdate = array_get($fieldValue, 'onupdate', 'restrict');
            $relationString = '$table->foreign(\'' . $fieldName . '\')';
            $relationString .= '->references(\'' . $relazioneCampo . '\')';
            $relationString .= '->on(\'' . $relazioneTabella . '\')';
            $relationString .= '->onDelete(\'' . $onDelete . '\')';
            $relationString .= '->onUpdate(\'' . $onUpdate . '\')';


        }

        switch ($index) {
            case 'index':
                $fieldString .= '->index()';
                break;
            case 'unique':
                $fieldString .= '->unique()';
                break;
            default:
                break;
        }

        $fieldString = $fieldString . ';';

        if ($relationString) {
            $fieldString = $fieldString . "\n" . $this->getIndent() . $relationString . ';';
        }
        return $fieldString;
    }


    public function saveModel()
    {


        $filename = base_path("app/Models/" . $this->modelName . '.php');

        if (file_exists($filename)) {
            return;
        }

        $stub = $this->files->get($this->getStub('model'));
        $variables = [];

        $columns_for_select_list = array_get($this->modelValues, 'columns_for_select_list', []);
        $columns_for_default_order = array_get($this->modelValues, 'columns_for_default_order', []);
        $columns_for_default_order_direction = array_get($this->modelValues, 'columns_for_default_order_direction', []);
        $columns_for_autocomplete = array_get($this->modelValues, 'columns_for_autocomplete', []);


//        $this->setApici($variables['columnsForSelectList']);

        $variables['columnsForSelectList'] = $this->implodeArray($columns_for_select_list);
        $variables['defaultOrderColumns'] = $this->implodeArray($columns_for_default_order_direction,
            $columns_for_default_order);
        $variables['columnsSearchAutoComplete'] = $this->implodeArray($columns_for_autocomplete);

        $traits = array_get($this->modelValues, 'traits', []);

        $traitString = '';
        foreach ($traits as $trait) {
            $traitString .= $this->getIndent() . 'use ' . $trait . ';' . "\n";
        }
        $variables['traits'] = $traitString;

        $relation_names = array_get($this->modelValues, 'relation_names', []);
        $relation_types = array_get($this->modelValues, 'relation_types', []);
        $relation_models = array_get($this->modelValues, 'relation_models', []);
        $relation_tables = array_get($this->modelValues, 'relation_tables', []);
        $relation_foreignkey = array_get($this->modelValues, 'relation_foreignkey', []);
        $relation_otherkey = array_get($this->modelValues, 'relation_otherkey', []);
        $relation_pivotkey = array_get($this->modelValues, 'relation_pivotkey', []);

        $variables['relationsData'] = $this->getRelationsDataString($relation_names, $relation_types, $relation_models,
            $relation_tables, $relation_foreignkey, $relation_otherkey, $relation_pivotkey);


        $variables['timestamps'] = $this->timestamps ? 'true' : 'false';
        $variables['ownerships'] = $this->ownerships ? 'true' : 'false';

        $stub = str_replace(
            '{{$migrationTable}}', $this->migrationTable, $stub
        );

        $stub = str_replace(
            '{{$modelClass}}', $this->modelName, $stub
        );

        foreach ($variables as $variableKey => $variableValue) {
            $stub = str_replace(
                '{{$' . $variableKey . '}}', $variableValue, $stub
            );
        }


        $this->files->put($filename, $stub);

        /*
         * SALVO I FIELDS NEL LANG
         */

        $configStubName = $this->getStub('config');
        $configStub = $this->files->get($configStubName);

        foreach ($this->langs['model'] as $modelLangFile) {
            $this->saveResourceFile($modelLangFile, $configStub, 'model');
        }

        foreach ($this->langs['fields'] as $fieldsLangFile) {
            $this->saveResourceFile($fieldsLangFile, $configStub, 'fields');
        }


    }

    protected function saveConfigFile($configFile, $configStub, $type = 'model')
    {
        $filename = config_path($configFile);

        $this->saveFile($filename, $configStub, $type = 'model');
    }

    protected function saveResourceFile($resourceFile, $configStub, $type = 'model')
    {
        $filename = resource_path($resourceFile);

        $this->saveFile($filename, $configStub, $type);
    }

    protected function saveFile($filename, $configStub, $type = 'model')
    {

        if (!$this->files->exists($filename)) {
            $parentDir = $this->files->dirname($filename);
            if (!$this->files->isDirectory($parentDir)) {
                $this->files->makeDirectory($parentDir,0755,true);
            }

            $this->files->put($filename,'<?php return []; ?>');
        }


        $langs = include $filename;

        $methodName = 'setConfigFile' . studly_case($type);

        $finalLangs = call_user_func_array([$this,$methodName],[$langs]);

        $modelConfigStub = str_replace(
            '{{$configArray}}', var_export($finalLangs, true), $configStub
        );

        $this->files->put($filename, $modelConfigStub);

    }

    protected function setConfigFileModel($langs) {
        $modelName = snake_case($this->modelName);
        if (!array_key_exists($modelName, $langs)) {
            $singolare = array_get($this->modelValues, 'lang_modello_singolare', $modelName);
            $plurale = array_get($this->modelValues, 'lang_modello_plurale', $modelName);

            $langs[$modelName] = "$singolare|$plurale";
        }

//        ksort($langs);

        return $langs;

    }
    protected function setConfigFileFields($langs) {

        foreach ($this->campi as $field => $fieldValue) {

            if (in_array($field,['timestamps','ownerships'])) {
                continue;
            }

            if (!array_key_exists($field, $langs)) {
                $langs[$field] = Lang::getM($field);
            }

        }

        return $langs;

//        ksort($langs);

    }
    protected function setConfigFileRoutes($config) {


        $modelName = snake_case($this->modelName);

        foreach ($this->configModelsFileRoutesEntry as $entry) {
            $routes = array_get($config, $entry, []);
            if (!array_key_exists($modelName, $routes)) {
                $routes[] = $modelName;
            }

            $config[$entry] = $routes;
        }

//        ksort($config);

        return $config;

    }

    public function saveModelConf()
    {


        $modelsConfsFileName = array_get($this->modelsConfsValues, 'nome_file_modelsconfs', '');

        $filename = public_path($modelsConfsFileName);

        $stub = $this->files->get($this->getStub('modelconf'));
        $variables = [];

        $searchValues = array_get($this->modelsConfsValues, 'search', []);
        $listValues = array_get($this->modelsConfsValues, 'list', []);
        $editValues = array_get($this->modelsConfsValues, 'edit', []);

        $variables['searchFields'] = $this->implodeArrayJsFields($searchValues['nome']);
        $variables['searchOperators'] = $this->implodeArrayJsOperators($searchValues['nome'],
            $searchValues['operator']);
        $variables['searchFieldsType'] = $this->implodeArrayJsFieldsType($searchValues['nome'], $searchValues['type']);

        $variables['listFields'] = $this->implodeArrayJsFields($listValues['nome']);
        $variables['listFieldsType'] = $this->implodeArrayJsFieldsType($listValues['nome'], $listValues['type']);
        $variables['listOrderFields'] = $this->implodeArrayJsOrderFields($listValues['nome'], $listValues['order']);

        $variables['editFields'] = $this->implodeArrayJsFields($editValues['nome']);
        $variables['editFieldsType'] = $this->implodeArrayJsFieldsType($editValues['nome'], $editValues['type']);

        $stub = str_replace(
            '{{$modelClass}}', $this->modelName, $stub
        );

        foreach ($variables as $variableKey => $variableValue) {
            $stub = str_replace(
                '{{$' . $variableKey . '}}', $variableValue, $stub
            );
        }


//        print_r($stub);

        $this->files->append($filename, $stub);


    }

    protected function implodeArray($values, $keys = null, $apici = true, $newline = false, $sep = '=>')
    {

        $string = '';

        if (is_null($keys)) {
            foreach ($values as $currentKey => $currentValue) {
                if ($currentValue == 'no') {
                    continue;
                }
                $string .= $apici ? "'" . $currentValue . "'" : $currentValue;
                $string .= ', ';
                $string .= $newline ? "\n" : "";
            }
            $string = trim($string, " ,\n");
            return $string;
        }

        foreach ($keys as $currentKey => $currentValue) {
            if ($currentValue == 'no') {
                continue;
            }
            $string .= $apici ? "'" . $currentValue . "'" : $currentValue;
            $string .= ' ' . $sep . ' ';
            $string .= $apici ? "'" . $values[$currentKey] . "'" : $values[$currentKey];
            $string .= ', ';
            $string .= $newline ? "\n" : "";
        }

        trim($string, " ,\n");

        return $string;
    }

    protected function implodeArrayJsFieldsType($keys, $values)
    {

        $string = '';

//        foreach ($keys as $currentKey => $currentValue) {
//            if ($values[$currentKey] == 'no') {
//                continue;
//            }
//            $string .= $this->getIndent(3) . "'" . $currentValue . "'";
//            $string .= ' : { ' . "\n";
//            $string .= $this->getIndent(4) . "type: '" . $values[$currentKey] . "'\n";
//            $string .= $this->getIndent(3) . '}, ' . "\n";
//        }

        foreach ($keys as $currentKey => $currentValue) {
            if ($values[$currentKey] == 'no') {
                continue;
            }

            $type = $values[$currentKey];
            $stub = $this->files->get($this->getStubInPath($type));

            $string .= $this->getIndent(3) . "'" . $currentValue . "'";
            $string .= ' : { ' . "\n";
            $string .= $stub;
            $string .= $this->getIndent(3) . '}, ' . "\n";
        }

        trim($string, " ,\n");

        return $string;
    }

    protected function implodeArrayJsOperators($keys, $values)
    {

        $string = '';

        foreach ($keys as $currentKey => $currentValue) {
            if ($values[$currentKey] == 'no') {
                continue;
            }
            $string .= $this->getIndent(3) . "'" . $currentValue . "' : '" . $values[$currentKey] . "',\n";
        }

        trim($string, " ,\n");

        return $string;
    }

    protected function implodeArrayJsOrderFields($keys, $values)
    {

        $string = '';

        foreach ($keys as $currentKey => $currentValue) {
            if ($values[$currentKey] == 'no') {
                continue;
            }
            $string .= $this->getIndent(3) . "'" . $currentValue . "' : '" . $currentValue . "',\n";
        }

        trim($string, " ,\n");

        return $string;
    }

    protected function implodeArrayJsFields($keys)
    {

        $string = '';

        foreach ($keys as $currentKey => $currentValue) {
            $string .= $this->getIndent(3) . "'" . $currentValue . "',\n";
        }

        trim($string, " ,\n");

        return $string;
    }

    protected function getRelationsDataString(
        $relation_names,
        $relation_types,
        $relation_models,
        $relation_tables,
        $relation_foreignkey,
        $relation_otherkey,
        $relation_pivotkey
    )
    {

        $string = '';

        foreach ($relation_names as $key => $value) {

            $string .= $this->getIndent();

            $string .= "'" . $value . "' => [";

            $string .= 'self::' . $relation_types[$key] . ", 'App\\Models\\";

            $string .= $relation_models[$key] . "'";

            if ($relation_tables[$key]) {
                $string .= ", 'table' => '" . $relation_tables[$key] . "'";
            }
            if ($relation_foreignkey[$key]) {
                $string .= ", 'foreignKey' => '" . $relation_foreignkey[$key] . "'";
            }
            if ($relation_otherkey[$key]) {
                $string .= ", 'otherKey' => '" . $relation_otherkey[$key] . "'";
            }
            if ($relation_pivotkey[$key]) {
                $string .= ", 'pivotKeys' => [" . $this->implodeArray(explode(',', $relation_pivotkey[$key])) . "]";
            }

            //'foreignKey' => '" . $relation_models[$key] . "',

            $string .= '],' . "\n";

        }

        return $string;

    }


    public function saveFieldsTranslations($fields) {
        $this->campi = $fields;
        $configStubName = $this->getStub('config');
        $configStub = $this->files->get($configStubName);

        foreach ($this->langs['fields'] as $fieldsLangFile) {
            $this->saveResourceFile($fieldsLangFile, $configStub, 'fields');
        }

    }

    /**
     * @return array
     */
    public function getCampi()
    {
        return $this->campi;
    }

    /**
     * @param array $campi
     */
    public function setCampi($campi)
    {
        $this->campi = $campi;
    }



//    protected function setApici($value) {
//        if (is_string($value)) {
//            return "'" . $value . "'";
//        }
//
//        if (is_array($value)) {
//            foreach ($value as $currentKey => $currentValue) {
//
//                $value[]
//
//            }
//        }
//    }
}