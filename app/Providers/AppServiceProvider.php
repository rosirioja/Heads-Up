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
        $this->app->bind('App\Contracts\UserInterface', 'App\Repositories\UserRepository');
        $this->app->bind('App\Contracts\CategoryInterface', 'App\Repositories\CategoryRepository');
        $this->app->bind('App\Contracts\RepetitionInterface', 'App\Repositories\RepetitionRepository');
        $this->app->bind('App\Contracts\AlertInterface', 'App\Repositories\AlertRepository');
    }
}
