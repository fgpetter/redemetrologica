<?php

use App\Mail\FaleconoscoMail;
use Illuminate\Support\Facades\Mail;

it('envia telefone de contato nacional somente em dígitos', function (string $telefone): void {
    Mail::fake();

    $this->post(route('faleconosco.submit'), [
        'name' => 'Pessoa de teste',
        'email' => 'teste@example.com',
        'phone' => $telefone,
    ])->assertRedirect(route('faleconosco.form'));

    Mail::assertQueued(FaleconoscoMail::class, function (FaleconoscoMail $mail): bool {
        return $mail->dados['phone'] === '11988887777';
    });
})->with([
    'com máscara' => '(11) 98888-7777',
    'somente dígitos' => '11988887777',
]);

it('rejeita telefone de contato inválido sem enviar e-mail', function (string $telefone): void {
    Mail::fake();

    $this->from(route('faleconosco.form'))
        ->post(route('faleconosco.submit'), [
            'name' => 'Pessoa de teste',
            'email' => 'teste@example.com',
            'phone' => $telefone,
        ])
        ->assertRedirect(route('faleconosco.form'))
        ->assertSessionHasErrors(['phone' => 'Informe um telefone válido com DDD.']);

    Mail::assertNothingSent();
})->with([
    'incompleto' => '(11) 9888-777',
    'letras' => '(11) 98888-7777abc',
    'código de país' => '+55 (11) 98888-7777',
]);
