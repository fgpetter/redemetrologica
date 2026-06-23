<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Salva no banco as ações do usuário
     *
     * @param  array|string  $info
     * @return void
     */
    protected function actionLog($info = null)
    {
        $data = [
            'entidade' => get_class($this),
            'usuario_nome' => Auth::user()->name ?? 'teste',
            'acao' => debug_backtrace()[1]['function'],
            'info' => json_encode($info),
        ];

        Log::channel('action')->info(json_encode($data));
    }
}
