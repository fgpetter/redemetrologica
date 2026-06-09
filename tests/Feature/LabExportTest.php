<?php

namespace Tests\Feature;

use App\Exports\LabExport;
use App\Models\AgendaInterlab;
use App\Models\Endereco;
use App\Models\Interlab;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_analista_duplica_linhas_por_analista(): void
    {
        $agenda = $this->criarAgendaComAvaliacao('ANALISTA');
        $inscrito = $this->criarInscritoCompleto($agenda, 'SENHA-INSCRITO');

        InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscrito->id,
            'nome' => 'Analista Um',
            'email' => 'analista1@example.com',
            'telefone' => '11999999999',
            'tag_senha' => 'TAG-UM',
        ]);

        InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscrito->id,
            'nome' => 'Analista Dois',
            'email' => 'analista2@example.com',
            'telefone' => '11988888888',
            'tag_senha' => 'TAG-DOIS',
        ]);

        $html = (new LabExport($agenda))->view()->render();

        $this->assertStringContainsString('TAG Senha</th>', $html);
        $this->assertStringContainsString('Nome Analista</th>', $html);
        $this->assertStringNotContainsString('Tag Senha Analista', $html);
        $this->assertStringContainsString('Analista Um', $html);
        $this->assertStringContainsString('Analista Dois', $html);
        $this->assertStringContainsString('TAG-UM', $html);
        $this->assertStringContainsString('TAG-DOIS', $html);
        $this->assertStringNotContainsString('SENHA-INSCRITO', $html);
        $this->assertEquals(2, substr_count($html, '<td>'.$inscrito->id.'</td>'));
    }

    public function test_export_analista_omite_inscritos_sem_analistas(): void
    {
        $agenda = $this->criarAgendaComAvaliacao('ANALISTA');
        $inscritoComAnalista = $this->criarInscritoCompleto($agenda);
        $inscritoSemAnalista = $this->criarInscritoCompleto($agenda);

        InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscritoComAnalista->id,
            'nome' => 'Analista Unico',
            'email' => 'analista@example.com',
            'telefone' => '11999999999',
            'tag_senha' => 'TAG-UNICO',
        ]);

        $html = (new LabExport($agenda))->view()->render();

        $this->assertEquals(1, substr_count($html, '<td>'.$inscritoComAnalista->id.'</td>'));
        $this->assertStringNotContainsString('<td>'.$inscritoSemAnalista->id.'</td>', $html);
    }

    public function test_export_laboratorial_mantem_tag_senha_do_inscrito(): void
    {
        $agenda = $this->criarAgendaComAvaliacao('LABORATORIAL');
        $inscrito = $this->criarInscritoCompleto($agenda, 'SENHA-LAB');

        InterlabAnalista::query()->create([
            'interlab_inscrito_id' => $inscrito->id,
            'nome' => 'Analista Ignorado',
            'email' => 'analista@example.com',
            'telefone' => '11999999999',
            'tag_senha' => 'TAG-ANALISTA',
        ]);

        $html = (new LabExport($agenda))->view()->render();

        $this->assertStringContainsString('TAG Senha</th>', $html);
        $this->assertStringNotContainsString('Nome Analista', $html);
        $this->assertStringContainsString('SENHA-LAB', $html);
        $this->assertStringNotContainsString('TAG-ANALISTA', $html);
        $this->assertEquals(1, substr_count($html, '<td>'.$inscrito->id.'</td>'));
    }

    private function criarAgendaComAvaliacao(string $avaliacao): AgendaInterlab
    {
        $interlab = Interlab::query()->create([
            'nome' => 'PEP Teste',
            'descricao' => 'Descricao',
            'tipo' => 'INTERLABORATORIAL',
            'avaliacao' => $avaliacao,
        ]);

        return AgendaInterlabFactory::new()->create([
            'interlab_id' => $interlab->id,
        ]);
    }

    private function criarInscritoCompleto(AgendaInterlab $agenda, ?string $tagSenha = null): InterlabInscrito
    {
        $pessoa = PessoaFactory::new()->create();
        $empresa = PessoaFactory::new()->create();
        $endereco = Endereco::query()->create(['pessoa_id' => $empresa->id]);
        $laboratorio = InterlabLaboratorio::query()->create([
            'empresa_id' => $empresa->id,
            'endereco_id' => $endereco->id,
            'nome' => fake()->company(),
        ]);

        return InterlabInscritoFactory::new()->create([
            'agenda_interlab_id' => $agenda->id,
            'pessoa_id' => $pessoa->id,
            'empresa_id' => $empresa->id,
            'laboratorio_id' => $laboratorio->id,
            'tag_senha' => $tagSenha,
        ]);
    }
}
