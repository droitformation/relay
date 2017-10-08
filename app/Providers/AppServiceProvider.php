<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
       $this->registerSendgridNewService();
    }

    /**
     * Newsletter service
     */
    protected function registerSendgridNewService(){

        $this->app->bind('App\Droit\Newsletter\Worker\SendgridInterface', function()
        {
            return new \App\Droit\Newsletter\Worker\SendgridService(
                new \SendGrid(env('SENDGRID_API'))
            );
        });
    }
}
