<?php

namespace Illuminate\Html;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(NavBuilder::class, function ($app) {
            $instance = new NavBuilder($app->make('request'));

            $instance->setUrlGenerator($app->make('url'));
            $instance->setTranslator($app->make('translator'));

            return $instance;
        });

        $this->app->singleton(DropdownBuilder::class, function ($app) {
            $instance = new DropdownBuilder($app->make('request'));

            $instance->setUrlGenerator($app->make('url'));
            $instance->setTranslator($app->make('translator'));

            return $instance;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            NavBuilder::class,
            DropdownBuilder::class,
        ];
    }

}
