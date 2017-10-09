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
        $this->app->bind('mailgun.client', function() {
            return \Http\Adapter\Guzzle6\Client::createWithConfig(['verify' => false]);
        });

        $this->registerSendgridNewService();
        $this->registerMailgunNewService();
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

    protected function registerMailgunNewService(){

        $this->app->bind('App\Droit\Newsletter\Worker\MailgunInterface', function()
        {
            return new \App\Droit\Newsletter\Worker\MailgunService(
                new \Mailgun\Mailgun('key-12354e9b024519a3be5b2f050615c5e1')
            );
        });
    }
}
