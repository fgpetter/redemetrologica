<?php

namespace App\Traits\Livewire\Suporte;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

trait RegistraErrosSuporte
{
    public function exception(Throwable $e, callable $stopPropagation): void
    {
        if ($e instanceof ValidationException) {
            Log::channel('validation')->info('Validação do suporte', ['user' => auth()->id(), 'errors' => $e->errors()]);
            $this->dispatch('suporte-erro', message: 'Revise os dados informados.');
        }
    }
}
