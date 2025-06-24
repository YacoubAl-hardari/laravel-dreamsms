<?php 
namespace DreamSms\LaravelDreamSms;


use DreamSms\LaravelDreamSms\DreamSms;
use Illuminate\Support\ServiceProvider;

class DreamSmsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/dreamsms.php' => config_path('dreamsms.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/dreamsms.php', 'dreamsms');

        $this->app->singleton(DreamSms::class, function ($app) {
            return new DreamSms(
                $app['config']['dreamsms.base_url'],
                $app['config']['dreamsms.user'],
                $app['config']['dreamsms.secret_key'],
                $app['config']['dreamsms.client_id'],
                $app['config']['dreamsms.client_secret'],
                $app['config']['dreamsms.sender_name']
            );
        });

        $this->app->alias(DreamSms::class, 'dreamsms');
    }
}
