<?php

namespace Gecche\Cupparis\ModelSkeleton;

use Gecche\AclGate\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ModelSkeletonServiceProvider extends ServiceProvider
{


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {


    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {

        $this->publishes([
            __DIR__ . '/../../config/cupparis-model-skeleton.php' => config_path('cupparis-model-skeleton.php'),
            __DIR__ . '/../../resources/stubs/migration' => base_path('stubs/migration'),
        ], 'public');

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'modelskeleton');

        // You can use Closure based composers
        // which will be used to resolve any data
        // in this case we will pass menu items from database
        View::composer('modelskeleton::*', function ($view) {
            $view->with('layoutView', Config::get('cupparis-model-skeleton.layout-view'));
        });

    }

}
