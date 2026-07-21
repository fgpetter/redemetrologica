<?php

namespace App\Actions;

use App\Mail\UserRegistered;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateUserForPessoaAction
{
    /**
     * Cria um usuário para uma pessoa, atribui permissão de cliente e envia email com credenciais
     *
     * @param  class-string  $mailableClass
     * @param  int  $delaySecs  Atraso em segundos até o envio na fila
     */
    public static function handle(Pessoa $pessoa, string $mailableClass = UserRegistered::class, int $delaySecs = 0): User
    {
        $existingUser = $pessoa->user_id ? $pessoa->user : null;

        // Se o usuário já existe, retorna o usuário
        if ($pessoa->user_id && $existingUser) {
            return $existingUser;
        }

        // user_id órfão (usuário apagado): limpa para recriar
        if ($pessoa->user_id && ! $existingUser) {
            $pessoa->update(['user_id' => null]);
        }

        // Gera senha aleatória
        $random_password = Str::random(8);

        // Cria o usuário
        $user = User::create([
            'name' => $pessoa->nome_razao,
            'email' => $pessoa->email,
            'password' => Hash::make($random_password),
            'temporary_password' => 1,
        ]);

        // Atribui permissão de cliente
        $user->givePermission('cliente');

        // Associa o usuário à pessoa
        $pessoa->update(['user_id' => $user->id]);

        // Prepara dados para o email
        $user_data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $random_password,
        ];

        // Envia email com as credenciais
        $mailable = new $mailableClass($user_data);

        if ($delaySecs > 0) {
            Mail::to($user->email)->later(now()->addSeconds($delaySecs), $mailable);
        } else {
            Mail::to($user->email)->send($mailable);
        }

        return $user;
    }
}
