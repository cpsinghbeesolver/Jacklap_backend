<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Events\MigrationsStarted;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Event::listen(MigrationsStarted::class, function () {
        //     Artisan::call('install:location-data');
        // });
        View::composer('*', function ($view) {
            $categories = Cache::remember('service_categories', 300, function () {    
                return ServiceCategory::where('status', 1)
                    ->orderBy('sort_order','ASC')
                    ->get();
            });
            $view->with('serviceCategories', $categories);
        });
    }
}
