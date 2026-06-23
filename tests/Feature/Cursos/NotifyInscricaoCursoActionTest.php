<?php

namespace Tests\Feature\Cursos;

use App\Actions\NotifyInscricaoCursoAction;
use App\Mail\ConfirmacaoInscricaoCursoNotification;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\Instrutor;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotifyInscricaoCursoActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_para_inscrito_enfileira_email_imediatamente(): void
    {
        Mail::fake();

        $agenda = $this->createAgendaCurso();
        $pessoa = Pessoa::query()->create([
            'nome_razao' => 'Pessoa PF',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        $inscrito = CursoInscrito::query()->create([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'nome' => 'Participante Teste',
            'email' => 'participante@example.com',
            'telefone' => '11999998888',
            'valor' => 150,
            'data_inscricao' => now(),
        ]);

        app(NotifyInscricaoCursoAction::class)->executeParaInscrito($inscrito, $agenda);

        Mail::assertQueued(ConfirmacaoInscricaoCursoNotification::class, function (ConfirmacaoInscricaoCursoNotification $mail) {
            return $mail->dados_email['participante_email'] === 'participante@example.com';
        });
    }

    public function test_execute_com_intervalo_agenda_envio_com_later(): void
    {
        Mail::fake();

        $agenda = $this->createAgendaCurso();

        app(NotifyInscricaoCursoAction::class)->execute($agenda, [
            [
                'nome' => 'Primeiro',
                'email' => 'primeiro@example.com',
                'telefone' => '',
                'empresa_nome' => 'Empresa LTDA',
            ],
            [
                'nome' => 'Segundo',
                'email' => 'segundo@example.com',
                'telefone' => '',
                'empresa_nome' => 'Empresa LTDA',
            ],
        ], intervaloSegundos: 5);

        Mail::assertQueued(ConfirmacaoInscricaoCursoNotification::class, 2);
    }

    public function test_email_vazio_nao_enfileira_notificacao(): void
    {
        Mail::fake();

        $agenda = $this->createAgendaCurso();

        app(NotifyInscricaoCursoAction::class)->execute($agenda, [
            [
                'nome' => 'Sem Email',
                'email' => '',
                'telefone' => '',
            ],
        ]);

        Mail::assertNothingQueued();
    }

    private function createAgendaCurso(): AgendaCursos
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso notify teste',
            'tipo_curso' => 'OFICIAL',
            'carga_horaria' => 8,
            'conteudo_programatico' => 'Conteúdo',
        ]);

        $pessoaInstrutor = Pessoa::query()->create([
            'nome_razao' => 'Instrutor',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        $instrutor = Instrutor::query()->create([
            'pessoa_id' => $pessoaInstrutor->id,
            'situacao' => true,
        ]);

        return AgendaCursos::query()->create([
            'uid' => (string) Str::uuid(),
            'curso_id' => $curso->id,
            'instrutor_id' => $instrutor->id,
            'status' => 'AGENDADO',
            'tipo_agendamento' => 'ONLINE',
            'inscricoes' => 1,
            'investimento' => 150,
            'investimento_associado' => 120,
            'data_inicio' => now()->addWeek(),
            'data_fim' => now()->addWeek(),
        ]);
    }
}
