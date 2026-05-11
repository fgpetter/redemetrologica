<?php

namespace App\Exceptions;

use App\Mail\NotifyInvalidEmailException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InvalidEmailException extends Exception
{
    public function __construct(public array $attributes)
    {
        $this->report();
    }

    public function report(): void
    {
        try {
            Mail::to('sistema@redemetrologica.com.br')->send(new NotifyInvalidEmailException($this->attributes));
        } catch (Throwable $exception) {
            Log::error($exception);
        }
    }
}
