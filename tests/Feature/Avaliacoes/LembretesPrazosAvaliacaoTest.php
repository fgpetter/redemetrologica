<?php

use App\Mail\AvisoLembretePrazoSemEmailMail;
use App\Mail\LembretePrazoAvaliacaoMail;
use App\Models\AgendaAvaliacao;
use App\Models\Laboratorio;
use Database\Factories\PessoaFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

function criarAvaliacaoLembretePrazo(array $atributos = [], ?string $emailLaboratorio = 'lab-lembrete@example.com'): AgendaAvaliacao
{
    $pessoa = PessoaFactory::new()->create([
        'nome_razao' => 'Laboratorio Lembrete Ltda',
        'tipo_pessoa' => 'PJ',
    ]);

    $laboratorio = Laboratorio::query()->create([
        'pessoa_id' => $pessoa->id,
        'nome_laboratorio' => 'Laboratorio Lembrete',
        'contato' => 'Contato',
        'telefone' => '11999999999',
        'email' => $emailLaboratorio,
        'responsavel_tecnico' => 'Responsável',
    ]);

    return AgendaAvaliacao::query()->create(array_merge([
        'laboratorio_id' => $laboratorio->id,
        'data_inicio' => '2026-08-01',
        'perc_lucro' => 15,
    ], $atributos));
}

test('comando proc laboratorio envia lembrete na sexta para prazo na segunda', function () {
    Mail::fake();
    Carbon::setTestNow('2026-09-04 00:00:00');

    criarAvaliacaoLembretePrazo([
        'data_proc_laboratorio' => '2026-09-07',
    ]);

    $this->artisan('avaliacoes:enviar-lembrete-proc-laboratorio')
        ->assertSuccessful();

    Mail::assertQueued(LembretePrazoAvaliacaoMail::class, function (LembretePrazoAvaliacaoMail $mail) {
        return $mail->hasTo('lab-lembrete@example.com')
            && $mail->rotuloPrazo === 'Procedimento Laboratório'
            && $mail->dataPrazoFormatada === '07/09/2026';
    });
});

test('comando proposta acoes envia lembrete na segunda para prazo na terca', function () {
    Mail::fake();
    Carbon::setTestNow('2026-09-07 00:00:00');

    criarAvaliacaoLembretePrazo([
        'data_proposta_acoes_corretivas' => '2026-09-08',
    ]);

    $this->artisan('avaliacoes:enviar-lembrete-proposta-acoes')
        ->assertSuccessful();

    Mail::assertQueued(LembretePrazoAvaliacaoMail::class, function (LembretePrazoAvaliacaoMail $mail) {
        return $mail->rotuloPrazo === 'Proposta Ações Corretivas'
            && $mail->dataPrazoFormatada === '08/09/2026';
    });
});

test('comando acoes corretivas envia lembrete na sexta para prazo na segunda', function () {
    Mail::fake();
    Carbon::setTestNow('2026-09-04 00:00:00');

    criarAvaliacaoLembretePrazo([
        'data_acoes_corretivas' => '2026-09-07',
    ]);

    $this->artisan('avaliacoes:enviar-lembrete-acoes-corretivas')
        ->assertSuccessful();

    Mail::assertQueued(LembretePrazoAvaliacaoMail::class, function (LembretePrazoAvaliacaoMail $mail) {
        return $mail->rotuloPrazo === 'Ações Corretivas'
            && $mail->dataPrazoFormatada === '07/09/2026';
    });
});

test('comando nao envia quando prazo esta fora da janela', function () {
    Mail::fake();
    Carbon::setTestNow('2026-09-03 00:00:00');

    criarAvaliacaoLembretePrazo([
        'data_proc_laboratorio' => '2026-09-07',
    ]);

    $this->artisan('avaliacoes:enviar-lembrete-proc-laboratorio')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});

test('comando nao envia no fim de semana', function () {
    Mail::fake();
    Carbon::setTestNow('2026-09-05 00:00:00');

    criarAvaliacaoLembretePrazo([
        'data_proc_laboratorio' => '2026-09-07',
    ]);

    $this->artisan('avaliacoes:enviar-lembrete-proc-laboratorio')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});

test('comando envia alerta interno quando laboratorio nao tem email', function () {
    Mail::fake();
    Carbon::setTestNow('2026-09-04 00:00:00');

    criarAvaliacaoLembretePrazo([
        'data_proc_laboratorio' => '2026-09-07',
    ], '');

    $this->artisan('avaliacoes:enviar-lembrete-proc-laboratorio')
        ->assertSuccessful();

    Mail::assertNotQueued(LembretePrazoAvaliacaoMail::class);
    Mail::assertQueued(AvisoLembretePrazoSemEmailMail::class, function (AvisoLembretePrazoSemEmailMail $mail) {
        return $mail->hasTo('sistema@redemetrologica.com.br')
            && $mail->rotuloPrazo === 'Procedimento Laboratório'
            && $mail->dataPrazoFormatada === '07/09/2026';
    });
});

afterEach(function (): void {
    Carbon::setTestNow();
});
