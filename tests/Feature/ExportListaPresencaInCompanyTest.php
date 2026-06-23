<?php

namespace Tests\Feature;

use App\Exports\CursoInscritosExport;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\Instrutor;
use App\Models\Permission;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExportListaPresencaInCompanyTest extends TestCase
{
    use RefreshDatabase;

    private function userComPermissaoPainel(): User
    {
        $user = User::factory()->create();

        $permission = Permission::withoutEvents(function (): Permission {
            return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
        });

        $user->permissions()->syncWithoutDetaching([$permission->id]);

        return $user;
    }

    /**
     * @return array{AgendaCursos, Pessoa}
     */
    private function criarAgendaComEmpresa(string $tipoAgendamento): array
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso Export Lista '.Str::uuid(),
            'tipo_curso' => 'OFICIAL',
        ]);

        $pessoaInstrutor = Pessoa::query()->create([
            'nome_razao' => 'Instrutor Export Lista',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        $instrutor = Instrutor::query()->create([
            'pessoa_id' => $pessoaInstrutor->id,
            'situacao' => true,
        ]);

        $empresaContratante = Pessoa::query()->create([
            'nome_razao' => 'Empresa Export Lista',
            'cpf_cnpj' => str_pad((string) random_int(10000000000000, 99999999999999), 14, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PJ',
        ]);

        $agenda = AgendaCursos::query()->create([
            'uid' => (string) Str::uuid(),
            'curso_id' => $curso->id,
            'instrutor_id' => $instrutor->id,
            'empresa_id' => $empresaContratante->id,
            'status' => 'AGENDADO',
            'tipo_agendamento' => $tipoAgendamento,
            'inscricoes' => 0,
            'site' => 0,
            'destaque' => 0,
            'data_inicio' => now()->toDateString(),
            'investimento' => 0,
            'investimento_associado' => 0,
        ]);

        $pessoaInscrito = Pessoa::query()->create([
            'nome_razao' => 'Pessoa Base Inscrito',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        return [$agenda, $pessoaInscrito];
    }

    public function test_export_lista_presenca_in_company_inclui_inscrito_com_valor_zero_na_view(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaComEmpresa('IN-COMPANY');

        $nomeUnico = 'ParticipanteValorZero'.Str::uuid();

        CursoInscrito::query()->create([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => $agenda->empresa_id,
            'nome' => $nomeUnico,
            'email' => 'zero@example.com',
            'valor' => 0,
            'data_inscricao' => now(),
        ]);

        $agenda->load(['curso', 'inscritos.pessoa', 'inscritos.empresa']);
        $html = (new CursoInscritosExport($agenda))->view()->render();

        $this->assertStringContainsString($nomeUnico, $html);
    }

    public function test_export_lista_presenca_online_nao_inclui_inscrito_com_valor_zero_na_view(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaComEmpresa('ONLINE');

        $nomeUnico = 'ParticipanteExcluido'.Str::uuid();

        CursoInscrito::query()->create([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => $agenda->empresa_id,
            'nome' => $nomeUnico,
            'email' => 'excluido@example.com',
            'valor' => 0,
            'data_inscricao' => now(),
        ]);

        $agenda->load(['curso', 'inscritos.pessoa', 'inscritos.empresa']);
        $html = (new CursoInscritosExport($agenda))->view()->render();

        $this->assertStringNotContainsString($nomeUnico, $html);
    }

    public function test_rota_export_lista_presenca_retorna_download_para_funcionario(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaComEmpresa('IN-COMPANY');

        CursoInscrito::query()->create([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => $agenda->empresa_id,
            'nome' => 'Nome Download Rota',
            'email' => 'rota@example.com',
            'valor' => 0,
            'data_inscricao' => now(),
        ]);

        $user = $this->userComPermissaoPainel();

        $response = $this->actingAs($user)->get(
            route('agendamento-curso.export-lista-presenca', $agenda)
        );

        $response->assertOk();
        $response->assertDownload();
    }
}
