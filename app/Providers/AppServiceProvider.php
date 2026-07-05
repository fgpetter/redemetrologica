<?php

namespace App\Providers;

use App\Actions\Interlab\SyncFornecedorAvaliacaoAction;
use App\Models\InterlabDespesa;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
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
        //
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();
        LogViewer::auth(function ($request) {
            if (env('APP_ENV', 'local') || $request->user()?->hasPermissionTo('admin')) {
                return true;
            } else {
                abort(404);
            }
        });

        InterlabDespesa::deleted(function (InterlabDespesa $despesa) {
            if ($despesa->agenda_interlab_id && $despesa->fornecedor_id) {
                app(SyncFornecedorAvaliacaoAction::class)->deleteIfSemDespesas(
                    $despesa->agenda_interlab_id,
                    $despesa->fornecedor_id
                );
            }
        });
    }
}
