<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Pessoa;
use Illuminate\Support\Str;
use App\Mail\UserRegistered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class CreateUserForPessoaAction
{
  /**
   * Cria um usuário para uma pessoa, atribui permissão de cliente e envia email com credenciais
   *
   * @param Pessoa $pessoa
   * @param string $nome
   * @param string $email
   * @return User
   */
  public static function handle(Pessoa $pessoa): User
  {
    // Se o usuário já existe, retorna o usuário
    if( $pessoa->user_id ) return $pessoa->user;

    // Gera senha aleatória
    $random_password = Str::random(8);

    // Cria o usuário
    $user = User::create([
      'name' => $pessoa->nome_razao,
      'email' => $pessoa->email,
      'password' => Hash::make($random_password),
      'temporary_password' => 1
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
    // Mail::to($user->email)->send(new UserRegistered($user_data));

    return $user;
  }
} 