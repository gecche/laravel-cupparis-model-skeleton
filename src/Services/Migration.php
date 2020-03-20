<?php namespace Gecche\Cupparis\ModelSkeleton\Services;

use App\Models\User;
use App\Services\Permissions;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Validator;
use Illuminate\Contracts\Auth\Registrar as RegistrarContract;
use Illuminate\Support\Str;


class Migration
{

    /*
     * SCRIPT PER RIMUOVERE I FILES CREATI DI PROVA
     *
     * php artisan migrate:rollback;
     *
     * rm -f app/Models/Prova.php;rm -f app/Models/Relations/Prova*.php;rm -f app/Policies/ProvaPolicy.php;rm -f public/js/ModelsConfs/M_Prova.php;rm -f database/migrations/*_prove_*;
     */

    protected $migrationValues = [];
    protected $modelValues = [];
    protected $modelsConfsValues = [];

    protected $migrationPath = 'database/migrations/';

    protected $migrationTable = '';
    protected $timestamps = true;
    protected $ownerships = true;

    protected $modelName = null;
    protected $campi = [];


    protected $configModelsListEntries = [];

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
        $this->configModelsListEntries = Arr::get($skeletonConfig,'config_models_list_entries',[]);


        $this->migrationValues = $migrationValues;
        $this->modelValues = $modelValues;
        $this->modelsConfsValues = $modelsConfsValues;
        $this->modelName = Arr::get($this->modelValues, 'nome_modello', '');


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

        $this->migrationTable = Arr::get($this->migrationValues, 'nome_tabella', '');

        $stub = str_replace(
            '{{$migrationTable}}', $this->migrationTable, $stub
        );

        $migrationClass = 'Create' . Str::studly($this->migrationTable)  . 'Table';

        $stub = str_replace(
            '{{$migrationClass}}', $migrationClass, $stub
        );

        $this->campi = Arr::get($this->migrationValues, 'campi', []);

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
        $type = Arr::get($fieldValue, 'tipo', 'string');
        $info = Arr::get($fieldValue, 'info', '');
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

        $nullable = Arr::get($fieldValue, 'nullable', 'no');
        if ($nullable == 'si') {
            $fieldString .= '->nullable()';
        }
        $default = Arr::get($fieldValue, 'default', '');
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

        $index = Arr::get($fieldValue, 'index', '');

        $relazioneTabella = Arr::get($fieldValue, 'relazione_tabella', '');
        $relazioneCampo = Arr::get($fieldValue, 'relazione_campo', '');

        $relationString = '';
        if ($relazioneTabella && $relazioneCampo) {
            //VINCOLO A CREARE UN INDICE NON UNICO SUL CAMPO PER LA RELAZIONE
            $index = 'index';

            $onDelete = Arr::get($fieldValue, 'ondelete', 'restrict');
            $onUpdate = Arr::get($fieldValue, 'onupdate', 'restrict');
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

        $columns_for_select_list = Arr::get($this->modelValues, 'columns_for_select_list', []);
        $columns_for_default_order = Arr::get($this->modelValues, 'columns_for_default_order', []);
        $columns_for_default_order_direction = Arr::get($this->modelValues, 'columns_for_default_order_direction', []);
        $columns_for_autocomplete = Arr::get($this->modelValues, 'columns_for_autocomplete', []);


//        $this->setApici($variables['columnsForSelectList']);

        $variables['columnsForSelectList'] = $this->implodeArray($columns_for_select_list);
        $variables['defaultOrderColumns'] = $this->implodeArray($columns_for_default_order_direction,
            $columns_for_default_order);
        $variables['columnsSearchAutoComplete'] = $this->implodeArray($columns_for_autocomplete);

        $traits = Arr::get($this->modelValues, 'traits', []);

        $traitString = '';
        foreach ($traits as $trait) {
            $traitString .= $this->getIndent() . 'use ' . $trait . ';' . "\n";
        }
        $variables['traits'] = $traitString;

        $relation_names = Arr::get($this->modelValues, 'relation_names', []);
        $relation_types = Arr::get($this->modelValues, 'relation_types', []);
        $relation_models = Arr::get($this->modelValues, 'relation_models', []);
        $relation_tables = Arr::get($this->modelValues, 'relation_tables', []);
        $relation_foreignkey = Arr::get($this->modelValues, 'relation_foreignkey', []);
        $relation_otherkey = Arr::get($this->modelValues, 'relation_otherkey', []);
        $relation_pivotkey = Arr::get($this->modelValues, 'relation_pivotkey', []);

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

        /*
         * SALVO ANCHE PER LE ROUTES
         * USO GLI STESSI METODI DEI LANGS
         */

        foreach ($this->configModelsListEntries as $configFile => $configEntries) {
            $configEntries = Arr::wrap($configEntries);
            $this->saveConfigFile($configFile.'.php', $configStub, 'listing', ['entries' => $configEntries]);
        }

    }

    public function savePolicy($modelValues = [])
    {




        $filename = base_path("app/Policies/" . $this->modelName . 'Policy.php');

        if (file_exists($filename)) {
            return;
        }

        $stub = $this->files->get($this->getStub('policy'));
        $variables = [];

//        $modelPlural = Arr::get($modelValues,'lang_modello_plurale',snake_case($this->modelName));

        $permissions = [
          'viewPermission' => 'view '.$this->modelName,
            'viewAllPermission' => 'view all '.$this->modelName,
            'updatePermission' => 'update '.$this->modelName,
            'deletePermission' => 'delete '.$this->modelName,
            'createPermission' => 'create '.$this->modelName,
            'listingPermission' => 'listing '.$this->modelName,
        ];

        $stub = str_replace(
            '{{$modelClass}}', $this->modelName, $stub
        );

        foreach ($permissions as $permissionKey => $permissionValue) {
            $stub = str_replace(
                '{{$' . $permissionKey . '}}', $permissionValue, $stub
            );
        }

        foreach ($variables as $variableKey => $variableValue) {
            $stub = str_replace(
                '{{$' . $variableKey . '}}', $variableValue, $stub
            );
        }


        $this->files->put($filename, $stub);

    }

    public function saveFoorm($migrationValues = [], $modelValues = [])
    {




        $filename = base_path("config/foorms/" . snake_case($this->modelName) . '.php');

        if (file_exists($filename)) {
            return;
        }

        $stub = $this->files->get($this->getStub('foorm'));

        Log::info("FOORM::: ");
        Log::info(print_r($migrationValues,true));
        Log::info(print_r($modelValues,true));

        $campi = Arr::get($migrationValues,'campi',[]);
        unset($campi['timestamps']);
        unset($campi['ownerships']);
        $relazioni = Arr::get($modelValues,'relation_names',[]);


        $campiFinali = [];
        foreach ($campi as $nomeCampo => $campo) {
            $campiFinali[$nomeCampo] = [];
        }

        $relazioniFinaliList = [];
        $relazioniFinaliEdit = [];
        foreach ($relazioni as $relazione) {
            $relazioniFinaliList[$relazione] = [
                'fields' => [

                ],
            ];
            $relazioniFinaliEdit[$relazione] = [
                'fields' => [

                ],
                'savetype' => [

                ],
            ];
        }

        $variables = [];

        $stub = str_replace(
            '{{$fields}}', $this->var_export54($campiFinali), $stub
        );
        $stub = str_replace(
            '{{$relations}}', $this->var_export54($relazioniFinaliList), $stub
        );
        $stub = str_replace(
            '{{$relationsEdit}}', $this->var_export54($relazioniFinaliEdit), $stub
        );

        foreach ($variables as $variableKey => $variableValue) {
            $stub = str_replace(
                '{{$' . $variableKey . '}}', $variableValue, $stub
            );
        }


        $this->files->put($filename, $stub);

    }

    protected function saveConfigFile($configFile, $configStub, $type = 'model', $params)
    {
        $filename = config_path($configFile);

        $this->saveFile($filename, $configStub, $type, $params);
    }

    protected function saveResourceFile($resourceFile, $configStub, $type = 'model')
    {
        $filename = resource_path($resourceFile);

        $this->saveFile($filename, $configStub, $type);
    }

    protected function saveFile($filename, $configStub, $type = 'model', $params = [])
    {

        if (!$this->files->exists($filename)) {
            $parentDir = $this->files->dirname($filename);
            if (!$this->files->isDirectory($parentDir)) {
                $this->files->makeDirectory($parentDir,0755,true);
            }

            $this->files->put($filename,'<?php return []; ?>');
        }


        $langs = include $filename;

        $methodName = 'setConfigFile' . Str::studly($type);

        $finalLangs = call_user_func_array([$this,$methodName],[$langs,$params]);

        $modelConfigStub = str_replace(
            '{{$configArray}}', var_export($finalLangs, true), $configStub
        );

        $this->files->put($filename, $modelConfigStub);

    }

    protected function setConfigFileModel($langs,$params = []) {
        $modelName = snake_case($this->modelName);
        if (!array_key_exists($modelName, $langs)) {
            $singolare = Arr::get($this->modelValues, 'lang_modello_singolare', $modelName);
            $plurale = Arr::get($this->modelValues, 'lang_modello_plurale', $modelName);

            $langs[$modelName] = "$singolare|$plurale";
        }

//        ksort($langs);

        return $langs;

    }
    protected function setConfigFileFields($langs,$params = []) {

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

    protected function setConfigFileListing($config,$params = []) {



        $modelName = snake_case($this->modelName);

        $entries = Arr::get($params,'entries',[]);
        foreach ($entries as $entry) {
            Log::info("LISTING 1:: ".$entry);
            $routes = Arr::get($config, $entry, []);
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


        $modelsConfsFileName = Arr::get($this->modelsConfsValues, 'nome_file_modelsconfs', '');

        $filename = public_path($modelsConfsFileName);

        $stub = $this->files->get($this->getStub('modelconf'));
        $variables = [];

        $searchValues = Arr::get($this->modelsConfsValues, 'search', []);
        $listValues = Arr::get($this->modelsConfsValues, 'list', []);
        $editValues = Arr::get($this->modelsConfsValues, 'edit', []);

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

    protected function var_export54($var, $indent="") {
        switch (gettype($var)) {
            case "string":
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        . ($indexed ? "" : $this->var_export54($key) . " => ")
                        . $this->var_export54($value, "$indent    ");
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
            case "boolean":
                return $var ? "TRUE" : "FALSE";
            default:
                return var_export($var, TRUE);
        }
    }
}
