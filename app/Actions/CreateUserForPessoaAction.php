<?php

namespace App\Actions;

use App\Mail\UserRegistered;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateUserForPessoaAction
{
    /**
     * Cria um usuário para uma pessoa, atribui permissão de cliente e envia email com credenciais
     *
     * @param  Pessoa  $pessoa  Pessoa vinculada ao usuário
     * @param  class-string<Mailable>  $mailableClass
     * @param  int  $delaySecs  Atraso em segundos até o envio na fila
     * @return User Usuário vinculado à pessoa
     */
    public static function handle(Pessoa $pessoa, string $mailableClass = UserRegistered::class, int $delaySecs = 0): User
    {
        [$user, $senha] = DB::transaction(function () use ($pessoa): array {
            $pessoaPersistida = Pessoa::query()->lockForUpdate()->findOrFail($pessoa->getKey());

            if ($pessoaPersistida->user) {
                return [$pessoaPersistida->user, null];
            }

            $senha = Str::random(8);
            $user = User::create([
                'name' => $pessoaPersistida->nome_razao,
                'email' => $pessoaPersistida->email,
                'password' => Hash::make($senha),
                'temporary_password' => 1,
            ]);

            $user->givePermission('cliente');
            $pessoaPersistida->update(['user_id' => $user->id]);

            return [$user, $senha];
        });

        $pessoa->user_id = $user->id;
        $pessoa->syncOriginalAttribute('user_id');
        $pessoa->setRelation('user', $user);

        if ($senha === null) {
            return $user;
        }

        // Prepara dados para o email
        $user_data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $senha,
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
