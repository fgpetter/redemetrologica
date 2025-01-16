<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Pessoa;
use Illuminate\Support\Str;
use App\Mail\UserRegistered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\RedirectsUsers;


trait RegistersUsers
{
    use RedirectsUsers;

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        $document = preg_replace('/[^0-9]/', '', $request['document']);

        // verifica se esse usuário foi importado do sistema antigo
        if($pessoa = Pessoa::where('cpf_cnpj', $document)->first()){

            if($pessoa->email) { // se pessoa tem cadastro com e-mail

                $mail = obfuscate_email($pessoa->email);

                if( !$pessoa->user?->exists ){ // se pessoa não possui usuário associado

                    // cria um usuário e envia e-mail com dados para primeiro login
                    $this->associaUsuario($pessoa);

                    return redirect('login')
                        ->withErrors(
                            ['document' => "<i class='ri-error-warning-line me-3 align-middle fs-lg text-danger'></i><strong>Ops!</strong>
                                <p class='mt-2'>O CPF informado já está associado ao usuário com email {$mail}. <br>
                                Verifique seu e-mail e siga o procedimento para definir sua senha e realizar o login. <br><br>
                                <span class='text-muted'>Caso você não tenha mais acesso a esse email ou não recebeu e-mail com os dados de acesso, 
                                entre em contato através do contato@redemetrologica.com.br</span></p>"]
                            );
                }

                // se pessoa já possui usuário associado
                return redirect('login')
                ->withErrors(
                    ['document' => "<i class='ri-error-warning-line me-3 align-middle fs-lg text-danger'></i><strong>Ops!</strong><br>
                        Seu usuário já está cadastrado. <br>
                        Faça login com seu e-mail e senha ou use a oção <strong> esqueci minha senha </strong>."]
                    );

            }

            // se pessoa tem cadastro mas não tem e-mail informado
            return redirect('login')
            ->withErrors(
                ['document' => "<i class='ri-error-warning-line me-3 align-middle fs-lg text-danger'></i><strong>Ops!</strong><br>
                    O CPF informado já possui cadastro em nosso sistema, mas ainda sem usuário para login. <br>
                    Solicite a atualização do seu cadastro através do email contato@redemetrologica.com.br"]
            );
        }


        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
                    ? new JsonResponse([], 201)
                    : redirect($this->redirectPath());
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        Pessoa::updateOrCreate(
            ['cpf_cnpj' => preg_replace('/[^0-9]/', '', $request['document'])],
            [
                'nome_razao' => $request['name'],
                'email' => $request['email'],
                'tipo_pessoa' => 'PF',
                'user_id' => $user->id
            ]
        );

        /*
          Adiciona permissão de cliente ao usuario
          e garante que ele não tenha acesso ao sistema
        */
        $user->givePermission('cliente');

        return redirect('painel');
    }

    private function associaUsuario(Pessoa $pessoa)
    {  
      $random_password = Str::random(8);
  
      $user = User::create([
        'name' => $pessoa->nome_razao,
        'email' => $pessoa->email,
        'password' => Hash::make($random_password),
        'temporary_password' => 1
      ]);
      $user->givePermission('cliente');
  
      $pessoa->update([ 'user_id' => $user->id ]);
  
      $user_data = [
        'name' => $user->name,
        'email' => $user->email,
        'password' => $random_password,
      ];
  
      Mail::to( $user->email )->bcc( 'contato@redemetrologica.com.br' )->send( new UserRegistered($user_data) );
      Log::channel('mailsent')->info("E-mail de cadastro enviado para \"{$user->email}\"");
    }
  
}
