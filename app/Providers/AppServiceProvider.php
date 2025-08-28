<?php

namespace App\Providers;

use App\Models\File;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
        Model::shouldBeStrict(! app()->isProduction());

        Route::bind('folder', function (string $folder) {
            return File::query()
                ->where('path', $folder)
                ->where('created_by', Auth::id())
                ->first();
        });
    }
}
