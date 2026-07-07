<?php

namespace Tests\Feature\Api\V1;

use App\Models\InterlabAnalista;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PepAgendaApiTest extends TestCase
{
    use RefreshDatabase;

    private const API_KEY = 'test-pep-api-key';

    public function test_listagem_retorna_401_sem_api_key(): void
    {
        $this->postJson('/api/v1/peps', [])
            ->assertStatus(401)
            ->assertJson(['message' => 'Chave de API não informada.']);
    }

    public function test_listagem_retorna_401_com_api_key_invalida(): void
    {
        $this->postJson('/api/v1/peps', ['api_key' => 'chave-errada'])
            ->assertStatus(401)
            ->assertJson(['message' => 'Chave de API inválida.']);
    }

    public function test_listagem_retorna_todas_agendas_sem_filtros(): void
    {
        $interlab = InterlabFactory::new()->create(['nome' => 'PEP Bebidas']);
        AgendaInterlabFactory::new()->create([
            'interlab_id' => $interlab->id,
            'status' => 'CONFIRMADO',
            'ano_referencia' => 2026,
        ]);
        AgendaInterlabFactory::new()->create([
            'interlab_id' => $interlab->id,
            'status' => 'AGENDADO',
            'ano_referencia' => 2025,
        ]);

        $response = $this->postJson('/api/v1/peps', ['api_key' => self::API_KEY]);

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure([
                'data' => [['nome_interlab', 'uid', 'status', 'ano_referencia']],
            ]);
    }

    public function test_listagem_filtra_por_status_ano_e_periodo(): void
    {
        $interlab = InterlabFactory::new()->create();

        $confirmado2026 = AgendaInterlabFactory::new()->create([
            'interlab_id' => $interlab->id,
            'status' => 'CONFIRMADO',
            'ano_referencia' => 2026,
            'data_inicio' => '2026-04-01',
        ]);

        AgendaInterlabFactory::new()->create([
            'interlab_id' => $interlab->id,
            'status' => 'AGENDADO',
            'ano_referencia' => 2025,
            'data_inicio' => '2025-01-01',
        ]);

        $response = $this->postJson('/api/v1/peps', [
            'api_key' => self::API_KEY,
            'status' => 'confirmado',
            'ano' => 2026,
            'datainicio' => '2026-01-01',
            'datafim' => '2026-12-31',
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uid', $confirmado2026->uid)
            ->assertJsonPath('data.0.status', 'confirmado');
    }

    public function test_listagem_retorna_422_para_status_invalido(): void
    {
        $this->postJson('/api/v1/peps', [
            'api_key' => self::API_KEY,
            'status' => 'invalido',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    public function test_detalhe_retorna_401_sem_api_key(): void
    {
        $agenda = AgendaInterlabFactory::new()->create();

        $this->postJson("/api/v1/pep/{$agenda->uid}", [])
            ->assertStatus(401);
    }

    public function test_detalhe_retorna_404_para_uid_inexistente(): void
    {
        $this->postJson('/api/v1/pep/uid-inexistente', ['api_key' => self::API_KEY])
            ->assertStatus(404)
            ->assertJson(['message' => 'Agenda PEP não encontrada.']);
    }

    public function test_detalhe_retorna_agenda_com_inscritos_agrupados_por_empresa(): void
    {
        $interlab = InterlabFactory::new()->create(['nome' => 'PEP Solos']);
        $agenda = AgendaInterlabFactory::new()->create(['interlab_id' => $interlab->id]);

        $empresaA = PessoaFactory::new()->create(['nome_razao' => 'Empresa A']);
        $empresaB = PessoaFactory::new()->create(['nome_razao' => 'Empresa B']);

        $inscritoA = InterlabInscritoFactory::new()->create([
            'agenda_interlab_id' => $agenda->id,
            'empresa_id' => $empresaA->id,
            'data_inscricao' => now()->subDay(),
            'tag_senha' => 'SEGREDO-A',
        ]);

        $inscritoB = InterlabInscritoFactory::new()->create([
            'agenda_interlab_id' => $agenda->id,
            'empresa_id' => $empresaB->id,
            'data_inscricao' => now(),
        ]);

        $response = $this->postJson("/api/v1/pep/{$agenda->uid}", ['api_key' => self::API_KEY]);

        $response->assertOk()
            ->assertJsonPath('data.agenda.uid', $agenda->uid)
            ->assertJsonPath('data.nome_interlab', 'PEP Solos')
            ->assertJsonCount(2, 'data.inscritos_por_empresa')
            ->assertJsonPath('data.inscritos_por_empresa.0.empresa.nome_razao', 'Empresa B')
            ->assertJsonPath('data.inscritos_por_empresa.0.inscritos.0.uid', $inscritoB->uid)
            ->assertJsonPath('data.inscritos_por_empresa.1.empresa.nome_razao', 'Empresa A')
            ->assertJsonPath('data.inscritos_por_empresa.1.inscritos.0.uid', $inscritoA->uid)
            ->assertJsonMissingPath('data.inscritos_por_empresa.1.inscritos.0.tag_senha');
    }

    public function test_detalhe_inclui_analistas_quando_pep_e_por_analista(): void
    {
        $interlab = InterlabFactory::new()->create(['avaliacao' => 'ANALISTA']);
        $agenda = AgendaInterlabFactory::new()->create(['interlab_id' => $interlab->id]);
        $inscrito = InterlabInscritoFactory::new()->create(['agenda_interlab_id' => $agenda->id]);
        InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscrito->id,
            'nome' => 'Analista Teste',
            'email' => 'analista@example.com',
            'telefone' => '11999999999',
        ]);

        $response = $this->postJson("/api/v1/pep/{$agenda->uid}", ['api_key' => self::API_KEY]);

        $response->assertOk()
            ->assertJsonCount(1, 'data.inscritos_por_empresa.0.inscritos.0.analistas');
    }
}
