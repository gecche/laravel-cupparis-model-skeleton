<?php

namespace Gecche\Cupparis\ModelSkeleton;

use Gecche\AclGate\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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


        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'modelskeleton');


    }

}
