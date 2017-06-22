<?php
namespace Lakipatel\DataTable;

use Illuminate\Support\ServiceProvider;
use Lakipatel\DataTable\DataTableCommand;

class DataTableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        $this->loadViewsFrom(__DIR__.'/views', 'data-table');

        $this->mergeConfigFrom(
            __DIR__.'/config/data-table.php', 'data-table'
        );

        $this->loadTranslationsFrom(__DIR__.'/translations', 'data-table');

        //config
        $this->publishes([
            __DIR__.'/config/data-table.php' => config_path('data-table.php')
        ], 'config');

        $this->publishes([
            __DIR__.'/translations' => resource_path('lang/vendor/data-table')
        ], 'translations');

        $this->publishes([
            __DIR__.'/views' => resource_path('views/vendor/data-table')
        ], 'views');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DataTableCommand::class
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}