<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
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
