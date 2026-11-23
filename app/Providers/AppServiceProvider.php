<?php

namespace App\Providers;

use App\Http\Middleware\CheckPermission;
use App\Queue\ScheduledEmailDatabaseConnector;
use App\Queue\ScheduledEmailGate;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\Events\JobAttempted;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Queue\Events\JobTimedOut;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Event;
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
        $this->app->singleton(ScheduledEmailGate::class);

        $this->callAfterResolving('queue', function (QueueManager $manager): void {
            $manager->addConnector('database-agendados', fn () => new ScheduledEmailDatabaseConnector($this->app['db']));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(JobQueued::class, function (JobQueued $evento): void {
            $this->app->make(ScheduledEmailGate::class)->enfileirado($evento, $this->app['queue']);
        });

        Event::listen([JobAttempted::class, JobTimedOut::class], function (JobAttempted|JobTimedOut $evento): void {
            $this->app->make(ScheduledEmailGate::class)->concluir($evento);
        });

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
