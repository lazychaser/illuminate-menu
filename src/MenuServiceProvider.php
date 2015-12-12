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
            $instance = new NavBuilder;

            $instance->setUrlGenerator($app->make('url'));
            $instance->setTranslator($app->make('translator'));
            $instance->setRequest($this->requestRebinding($instance));

            return $instance;
        });

        $this->app->singleton(DropdownBuilder::class, function ($app) {
            $instance = new DropdownBuilder;

            $instance->setUrlGenerator($app->make('url'));
            $instance->setTranslator($app->make('translator'));
            $instance->setRequest($this->requestRebinding($instance));

            return $instance;
        });
    }

    /**
     * @param BaseMenuBuilder $instance
     *
     * @return mixed
     */
    protected function requestRebinding(BaseMenuBuilder $instance)
    {
        return $this->app->rebinding('request', function ($request) use ($instance) {
            $instance->setRequest($request);
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
