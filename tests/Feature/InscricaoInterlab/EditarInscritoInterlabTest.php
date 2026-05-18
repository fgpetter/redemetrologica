<?php

namespace Tests\Feature\InscricaoInterlab;

use App\Livewire\Interlab\EditarInscrito;
use App\Models\Endereco;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\User;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\LancamentoFinanceiroFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditarInscritoInterlabTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->createOne();

        $this->actingAs($user);
    }

    public function test_salvar_atualiza_inscricao_laboratorio_e_endereco(): void
    {
        $inscrito = $this->criarInscritoCompleto();

        Livewire::test(EditarInscrito::class)
            ->call('abrir', $inscrito->id)
            ->call('carregarInscrito')
            ->set('form.informacoes_inscricao', 'Observação atualizada')
            ->set('form.responsavel_tecnico', 'Dr. Teste')
            ->set('form.email', 'lab@teste.com')
            ->set('form.tag_senha', 'TAG-01')
            ->set('form.labNome', 'Laboratório Atualizado')
            ->set('form.cep', '01310100')
            ->set('form.endereco', 'Av Paulista')
            ->set('form.bairro', 'Bela Vista')
            ->set('form.cidade', 'São Paulo')
            ->set('form.uf', 'SP')
            ->set('form.complemento', 'Sala 1')
            ->call('salvar')
            ->assertHasNoErrors();

        $inscrito->refresh();

        $this->assertSame('Observação atualizada', $inscrito->informacoes_inscricao);
        $this->assertSame('Dr. Teste', $inscrito->responsavel_tecnico);
        $this->assertSame('lab@teste.com', $inscrito->email);
        $this->assertSame('TAG-01', $inscrito->tag_senha);

        $lab = $inscrito->laboratorio;
        $this->assertNotNull($lab);
        $this->assertSame('Laboratório Atualizado', $lab->nome);

        $endereco = $lab->endereco;
        $this->assertNotNull($endereco);
        $this->assertSame('01310-100', $endereco->cep);
        $this->assertSame('Av Paulista', $endereco->endereco);
        $this->assertSame('Bela Vista', $endereco->bairro);
        $this->assertSame('São Paulo', $endereco->cidade);
        $this->assertSame('SP', $endereco->uf);
        $this->assertSame('Sala 1', $endereco->complemento);
    }

    public function test_salvar_atualiza_valor_e_gera_lancamento(): void
    {
        $inscrito = $this->criarInscritoCompleto([
            'valor' => null,
            'lancamento_financeiro_id' => null,
        ]);

        Livewire::test(EditarInscrito::class)
            ->call('abrir', $inscrito->id)
            ->call('carregarInscrito')
            ->set('form.valor', '1.500,00')
            ->call('salvar')
            ->assertHasNoErrors();

        $inscrito->refresh();

        $this->assertSame('1500.00', (string) $inscrito->valor);
        $this->assertNotNull($inscrito->lancamento_financeiro_id);
        $this->assertDatabaseCount('lancamentos_financeiros', 1);
        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $inscrito->lancamento_financeiro_id,
            'valor' => 1500.00,
            'status' => 'PROVISIONADO',
            'agenda_interlab_id' => $inscrito->agenda_interlab_id,
        ]);
    }

    public function test_salvar_atualiza_lancamento_existente_sem_criar_novo(): void
    {
        $inscrito = $this->criarInscritoCompleto([
            'valor' => 100.00,
            'lancamento_financeiro_id' => null,
        ]);

        $lancamentoExistente = LancamentoFinanceiroFactory::new()->create([
            'pessoa_id' => $inscrito->empresa_id,
            'agenda_interlab_id' => $inscrito->agenda_interlab_id,
            'valor' => 100.00,
        ]);

        $inscrito->update([
            'lancamento_financeiro_id' => $lancamentoExistente->id,
        ]);

        Livewire::test(EditarInscrito::class)
            ->call('abrir', $inscrito->id)
            ->call('carregarInscrito')
            ->set('form.valor', '2.000,00')
            ->call('salvar')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('lancamentos_financeiros', 1);
        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $lancamentoExistente->id,
            'valor' => 2000.00,
        ]);
        $this->assertDatabaseHas('interlab_inscritos', [
            'id' => $inscrito->id,
            'valor' => 2000.00,
        ]);
    }

    public function test_abrir_carrega_opcoes_de_responsavel_como_array_leve(): void
    {
        $inscrito = $this->criarInscritoCompleto();

        PessoaFactory::new()->count(2)->create(['tipo_pessoa' => 'PF']);

        $component = Livewire::test(EditarInscrito::class)
            ->call('abrir', $inscrito->id)
            ->call('carregarInscrito');

        $pessoas = $component->get('pessoas');

        $this->assertIsArray($pessoas);
        $this->assertNotEmpty($pessoas);
        $this->assertArrayHasKey('id', $pessoas[0]);
        $this->assertArrayHasKey('cpf_cnpj', $pessoas[0]);
        $this->assertArrayHasKey('nome_razao', $pessoas[0]);
        $this->assertArrayNotHasKey('tipo_pessoa', $pessoas[0]);
        $this->assertNotContains($inscrito->pessoa_id, array_column($pessoas, 'id'));
    }

    public function test_abrir_define_carregando_antes_de_carregar_dados(): void
    {
        $inscrito = $this->criarInscritoCompleto();

        $component = Livewire::test(EditarInscrito::class);

        $component->call('abrir', $inscrito->id);

        $this->assertTrue($component->get('carregando'));
        $this->assertNull($component->get('inscrito'));

        $component->call('carregarInscrito');

        $this->assertFalse($component->get('carregando'));
        $this->assertNotNull($component->get('inscrito'));
        $this->assertSame($inscrito->id, $component->get('inscrito')->id);
    }

    public function test_carregar_inscrito_id_inexistente_limpa_estado_e_dispara_erro(): void
    {
        $idInexistente = (int) (InterlabInscrito::query()->max('id') ?? 0) + 99_999;

        Livewire::test(EditarInscrito::class)
            ->call('abrir', $idInexistente)
            ->call('carregarInscrito')
            ->assertSet('inscrito', null)
            ->assertSet('inscritoId', null)
            ->assertSet('carregando', false)
            ->assertDispatched('show-error-alert', message: 'Inscrição não encontrada.')
            ->assertDispatched('offcanvas:close');
    }

    public function test_alterar_responsavel_atualiza_pessoa_id(): void
    {
        $inscrito = $this->criarInscritoCompleto();

        $novoResponsavel = PessoaFactory::new()->create([
            'tipo_pessoa' => 'PF',
            'nome_razao' => 'Nova Pessoa',
            'cpf_cnpj' => '52998224725',
        ]);

        Livewire::test(EditarInscrito::class)
            ->call('abrir', $inscrito->id)
            ->call('carregarInscrito')
            ->set('novoResponsavelId', (string) $novoResponsavel->id)
            ->call('alterarResponsavel')
            ->assertHasNoErrors();

        $inscrito->refresh();

        $this->assertSame($novoResponsavel->id, $inscrito->pessoa_id);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function criarInscritoCompleto(array $overrides = []): InterlabInscrito
    {
        $pessoa = PessoaFactory::new()->create(['tipo_pessoa' => 'PF']);
        $empresa = PessoaFactory::new()->create(['tipo_pessoa' => 'PJ']);

        $endereco = Endereco::query()->create([
            'pessoa_id' => $empresa->id,
            'info' => 'Laboratório Interlab',
            'cep' => '01001000',
            'endereco' => 'Praça da Sé',
            'complemento' => null,
            'bairro' => 'Sé',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
        ]);

        $laboratorio = InterlabLaboratorio::query()->create([
            'empresa_id' => $empresa->id,
            'endereco_id' => $endereco->id,
            'nome' => 'Lab Original',
        ]);

        return InterlabInscritoFactory::new()->create(array_merge([
            'pessoa_id' => $pessoa->id,
            'empresa_id' => $empresa->id,
            'laboratorio_id' => $laboratorio->id,
            'informacoes_inscricao' => 'Antigo',
            'responsavel_tecnico' => 'Antigo RT',
            'email' => 'antigo@teste.com',
            'tag_senha' => 'OLD',
        ], $overrides));
    }
}
