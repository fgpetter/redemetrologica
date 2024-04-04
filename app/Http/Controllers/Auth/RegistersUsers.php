<?php

namespace App\Http\Controllers\Auth;

use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
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

        if($pessoa = Pessoa::where('cpf_cnpj', $request['document'])->first()){
            $mail = obfuscate_email($pessoa->user->email);
            return redirect('login')->withErrors(['document' => "O CPF informado já está associado ao usuário {$mail}."]);
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
        Pessoa::create([
            'uid' => config('hashing.uid'),
            'nome_razao' => $request['name'],
            'cpf_cnpj' => preg_replace('/[^0-9]/', '', $request['document']),
            'tipo_pessoa' => 'PF',
            'email' => $request['email'],
            'user_id' => $user->id
        ]);

        return redirect('painel');
    }
}
