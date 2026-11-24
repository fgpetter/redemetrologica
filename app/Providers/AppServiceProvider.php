<?php

namespace App\Providers;

use App\Http\Middleware\CheckPermission;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Opcodes\LogViewer\Facades\LogViewer;

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
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();
        Livewire::addPersistentMiddleware(CheckPermission::class);
        LogViewer::auth(function ($request) {
            if (env('APP_ENV', 'local') || $request->user()?->hasPermissionTo('admin')) {
                return true;
            } else {
                abort(404);
            }
        });
    }
}
