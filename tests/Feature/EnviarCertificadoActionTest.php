<?php

namespace Tests\Feature;

use App\Actions\EnviarCertificadoAction;
use App\Jobs\EnviarLinkCertificadoJob;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\Instrutor;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnviarCertificadoActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_usa_carga_horaria_da_agenda_quando_preenchida(): void
    {
        Queue::fake();

        $inscrito = $this->criarInscritoComCargaHoraria(agendaCargaHoraria: 16, cursoCargaHoraria: 40);

        $dadosDoc = app(EnviarCertificadoAction::class)->execute($inscrito);

        $this->assertSame(16, $dadosDoc->content['carga_horaria']);
        Queue::assertPushed(EnviarLinkCertificadoJob::class);
    }

    public function test_usa_carga_horaria_do_curso_quando_agenda_nao_define(): void
    {
        Queue::fake();

        $inscrito = $this->criarInscritoComCargaHoraria(agendaCargaHoraria: null, cursoCargaHoraria: 40);

        $dadosDoc = app(EnviarCertificadoAction::class)->execute($inscrito);

        $this->assertSame(40, $dadosDoc->content['carga_horaria']);
        Queue::assertPushed(EnviarLinkCertificadoJob::class);
    }

    private function criarInscritoComCargaHoraria(?int $agendaCargaHoraria, int $cursoCargaHoraria): CursoInscrito
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso certificado teste',
            'tipo_curso' => 'OFICIAL',
            'carga_horaria' => $cursoCargaHoraria,
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

        $empresa = Pessoa::query()->create([
            'nome_razao' => 'Empresa',
            'cpf_cnpj' => str_pad((string) random_int(10000000000000, 99999999999999), 14, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PJ',
        ]);

        $agenda = AgendaCursos::query()->create([
            'uid' => (string) Str::uuid(),
            'curso_id' => $curso->id,
            'instrutor_id' => $instrutor->id,
            'status' => 'REALIZADO',
            'tipo_agendamento' => 'ONLINE',
            'inscricoes' => 1,
            'investimento' => 100,
            'investimento_associado' => 80,
            'data_inicio' => now()->subDay(),
            'data_fim' => now()->subDay(),
            'carga_horaria' => $agendaCargaHoraria,
        ]);

        $pessoa = Pessoa::query()->create([
            'nome_razao' => 'Participante',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        return CursoInscrito::query()->create([
            'agenda_curso_id' => $agenda->id,
            'pessoa_id' => $pessoa->id,
            'empresa_id' => $empresa->id,
            'nome' => 'Participante',
            'email' => 'participante@example.com',
            'valor' => 100,
            'data_inscricao' => now(),
        ]);
    }
}
