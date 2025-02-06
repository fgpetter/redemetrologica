<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use App\Mail\NotifyRegisterInvalid;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyRegisterException;

class InvalidEmailRegisterException extends Exception
{
    public function __construct(public $pessoa) {
        $this->report();
    }


     public function report(): void
     {
         $content['pessoa'] = $this->pessoa->nome_razao;
         $content['pessoa_uid'] = $this->pessoa->uid;
         $content['email'] = $this->pessoa->email;
         $content['url'] = request()->url();
         $content['request'] = collect(request()->except(['_token', 'password', 'password_confirmation']))
            ->toJson(JSON_PRETTY_PRINT);
         $content['ip'] = request()->ip();

         Mail::to('ti@redemetrologica.com.br')->send(new NotifyRegisterException($content));
         Mail::to('contato@redemetrologica.com.br')->send(new NotifyRegisterInvalid($content));
         try {
 
         } catch (Throwable $exception) {
            Log::error($exception);
         }
 
     }

     public function render()
     {
        return redirect('login')
        ->withErrors(
            ['document' => "<i class='ri-error-warning-line me-3 align-middle fs-lg text-danger'></i><strong>Ops!</strong><br>
                O CPF informado já possui cadastro em nosso sistema, mas ainda sem usuário para login. <br>
                Solicite a atualização do seu cadastro através do email contato@redemetrologica.com.br"]
        );

     }

}
