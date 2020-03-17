<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$namespace = \Illuminate\Support\Facades\Config::get('cupparis-model-skeleton.namespace',"Gecche\\Cupparis\\ModelSkeleton\\Controllers");
Route::group([
    'namespace' => $namespace,
    'prefix' => 'cupparis/modelskeleton',
    'middleware' => ['web']
], function () {

    Route::get('index')
        ->uses('ModelSkeletonController@getIndex');

    Route::get('migration')
        ->uses('ModelSkeletonController@getMigration');
    Route::post('migration')
        ->uses('ModelSkeletonController@postMigration');
    Route::post('migration2')
        ->uses('ModelSkeletonController@postMigration2');
    Route::post('migration3')
        ->uses('ModelSkeletonController@postMigration3');

    Route::get('model')
        ->uses('ModelSkeletonController@getModel');
    Route::post('model')
        ->uses('ModelSkeletonController@postModel');
    Route::post('model2')
        ->uses('ModelSkeletonController@postModel2');
    Route::post('model3')
        ->uses('ModelSkeletonController@postModel3');

    Route::get('modelconf')
        ->uses('ModelSkeletonController@getModelconf');
    Route::post('modelconf')
        ->uses('ModelSkeletonController@postModelconf');
    Route::post('modelconf2')
        ->uses('ModelSkeletonController@postModelconf2');

});
