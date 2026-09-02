<?php

use App\Livewire\Avaliacoes\MediaAvaliacoesTable;
use App\Models\AgendaAvaliacao;
use App\Models\Laboratorio;
use App\Models\Permission;
use App\Models\User;
use Database\Factories\PessoaFactory;
use Livewire\Livewire;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\FakePdfBuilder;

function usuarioMediaAvaliacoes(): User
{
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(function (): Permission {
        return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
    });
    $user->permissions()->syncWithoutDetaching([$permission->id]);

    return $user;
}

function criarAvaliacaoMedia(array $atributos = [], ?Laboratorio $laboratorio = null): AgendaAvaliacao
{
    if ($laboratorio === null) {
        $pessoa = PessoaFactory::new()->create([
            'nome_razao' => 'Laboratorio Media Ltda',
            'tipo_pessoa' => 'PJ',
        ]);

        $laboratorio = Laboratorio::query()->create([
            'pessoa_id' => $pessoa->id,
            'nome_laboratorio' => 'Laboratorio Media',
            'contato' => 'Contato',
            'telefone' => '11999999999',
            'email' => 'lab-media@example.com',
            'responsavel_tecnico' => 'Responsável',
        ]);
    }

    return AgendaAvaliacao::query()->create(array_merge([
        'laboratorio_id' => $laboratorio->id,
        'data_inicio' => '2026-01-10',
    ], $atributos));
}

test('relatorio agrupa mes e distingue nao respondida da media', function () {
    $avaliacaoRespondida = criarAvaliacaoMedia([
        'data_inicio' => '2026-01-10',
        'med_pesquisa' => 9.86,
    ]);
    $avaliacaoRespondida->pesquisa()->create(['preenchido_em' => now()]);

    $avaliacaoPendente = criarAvaliacaoMedia([
        'data_inicio' => '2026-01-20',
        'laboratorio_id' => $avaliacaoRespondida->laboratorio_id,
    ]);
    $avaliacaoPendente->pesquisa()->create([]);

    Livewire::actingAs(usuarioMediaAvaliacoes())
        ->test(MediaAvaliacoesTable::class)
        ->set('dataIni', '2026-01-01')
        ->set('dataFim', '2026-01-31')
        ->assertSee('Mês: 01/2026')
        ->assertSee('Não Respondida')
        ->assertSee('9,86');
});

test('imprimir gera pdf com view e conteudo do relatorio', function () {
    $fake = new class extends FakePdfBuilder
    {
        public function save(string $path): self
        {
            parent::save($path);

            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }

            file_put_contents($path, '%PDF-1.4');

            return $this;
        }
    };

    Pdf::swap($fake);

    $avaliacaoRespondida = criarAvaliacaoMedia([
        'data_inicio' => '2026-01-10',
        'med_pesquisa' => 9.86,
    ]);
    $avaliacaoRespondida->pesquisa()->create(['preenchido_em' => now()]);

    $avaliacaoPendente = criarAvaliacaoMedia([
        'data_inicio' => '2026-01-20',
        'laboratorio_id' => $avaliacaoRespondida->laboratorio_id,
    ]);
    $avaliacaoPendente->pesquisa()->create([]);

    Livewire::actingAs(usuarioMediaAvaliacoes())
        ->test(MediaAvaliacoesTable::class)
        ->set('dataIni', '2026-01-01')
        ->set('dataFim', '2026-01-31')
        ->call('imprimir')
        ->assertFileDownloaded('media-avaliacoes.pdf');

    Pdf::assertViewIs('pdf.avaliacoes.media');
    Pdf::assertSee('Não Respondida');
    Pdf::assertSee('9,86');
    Pdf::assertSee('Mês: 01/2026');
});

test('limpar reseta filtros do relatorio', function () {
    Livewire::actingAs(usuarioMediaAvaliacoes())
        ->test(MediaAvaliacoesTable::class)
        ->set('dataIni', '2026-01-01')
        ->set('dataFim', '2026-01-31')
        ->set('search', 'Laboratorio')
        ->call('limpar')
        ->assertSet('dataIni', '')
        ->assertSet('dataFim', '')
        ->assertSet('search', '');
});
