<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Services\DatabaseChangeLogger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        // Log every database write operation (INSERT/UPDATE/DELETE) to a daily SQL file
        // Files are saved to: storage/app/db-changelog/changelog-YYYY-MM-DD.sql
        if (!app()->runningInConsole() || app()->runningUnitTests()) {
            (new DatabaseChangeLogger())->listen();
        }
    }
}
