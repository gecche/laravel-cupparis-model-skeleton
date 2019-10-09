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
Route::group(['middleware' => ['web']], function () {

    Route::get('/cupparis/modelskeleton/index')
        ->uses('Gecche\Cupparis\ModelSkeleton\Controllers\ModelSkeletonController@getIndex');

    Route::get('/cupparis/modelskeleton/migration')
        ->uses('Gecche\Cupparis\ModelSkeleton\Controllers\ModelSkeletonController@getMigration');
    Route::post('/cupparis/modelskeleton/migration')
        ->uses('Gecche\Cupparis\ModelSkeleton\Controllers\ModelSkeletonController@postMigration');
    Route::post('/cupparis/modelskeleton/migration2')
        ->uses('Gecche\Cupparis\ModelSkeleton\Controllers\ModelSkeletonController@postMigration2');
    Route::post('/cupparis/modelskeleton/migration3')
        ->uses('Gecche\Cupparis\ModelSkeleton\Controllers\ModelSkeletonController@postMigration3');

    Route::get('/cupparis/modelskeleton/model')
        ->uses('Gecche\Cupparis\ModelSkeleton\Controllers\ModelSkeletonController@getModel');
    Route::post('/cupparis/modelskeleton/model')
        ->uses('Gecche\Cupparis\ModelSkeleton\Controllers\ModelSkeletonController@postModel');
    Route::post('/cupparis/modelskeleton/model2')
        ->uses('Gecche\Cupparis\ModelSkeleton\Controllers\ModelSkeletonController@postModel2');
    Route::post('/cupparis/modelskeleton/model3')
        ->uses('Gecche\Cupparis\ModelSkeleton\Controllers\ModelSkeletonController@postModel3');

    Route::get('/cupparis/modelskeleton/modelconf')
        ->uses('Gecche\Cupparis\ModelSkeleton\Controllers\ModelSkeletonController@getModelconf');
    Route::post('/cupparis/modelskeleton/modelconf')
        ->uses('Gecche\Cupparis\ModelSkeleton\Controllers\ModelSkeletonController@postModelconf');
    Route::post('/cupparis/modelskeleton/modelconf2')
        ->uses('Gecche\Cupparis\ModelSkeleton\Controllers\ModelSkeletonController@postModelconf2');

});
