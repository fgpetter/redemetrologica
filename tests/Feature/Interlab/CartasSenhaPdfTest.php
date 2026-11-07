<?php

use App\Models\DadosGeraDoc;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\FakePdfBuilder;

it('preenche o ano e remove o PDF anterior apenas na aplicação', function (string $tipo) {
    $disco = Storage::fake('public');
    $agenda = AgendaInterlabFactory::new()->create(['ano_referencia' => 2026]);
    $inscrito = InterlabInscritoFactory::new()->create(['agenda_interlab_id' => $agenda->id]);
    $carta = DadosGeraDoc::create([
        'tipo' => $tipo,
        'file_name' => 'public/docs/senhas/legado.pdf',
        'content' => ['participante_id' => $inscrito->id, 'tag_senha' => 'cAR_469'],
    ]);
    $disco->put('docs/senhas/legado.pdf', 'PDF antigo');
    $link = $carta->link;

    $this->artisan('interlab:preencher-ano-cartas-senha', ['--dry-run' => true])->assertSuccessful();
    expect($carta->fresh()->content)->not->toHaveKey('ano_referencia');
    $disco->assertExists('docs/senhas/legado.pdf');

    $this->artisan('interlab:preencher-ano-cartas-senha')->assertSuccessful();
    $carta->refresh();
    expect($carta->content)->toBe(['participante_id' => $inscrito->id, 'tag_senha' => 'cAR_469', 'ano_referencia' => 2026])
        ->and($carta->link)->toBe($link)
        ->and($carta->getRawOriginal('file_name'))->toBeNull();
    $disco->assertMissing('docs/senhas/legado.pdf');

    $this->artisan('interlab:preencher-ano-cartas-senha')
        ->expectsOutputToContain('Cartas atualizadas: 0')
        ->assertSuccessful();
})->with(['tag_senha', 'tag_senha_analista']);

it('reporta cartas sem origem ou com referência inválida e preserva o ano já preenchido', function () {
    $disco = Storage::fake('public');
    $agenda = AgendaInterlabFactory::new()->create(['ano_referencia' => null]);
    $inscrito = InterlabInscritoFactory::new()->create(['agenda_interlab_id' => $agenda->id]);
    $semInscrito = DadosGeraDoc::create(['tipo' => 'tag_senha', 'content' => []]);
    $semAno = DadosGeraDoc::create([
        'tipo' => 'tag_senha',
        'content' => ['participante_id' => $inscrito->id],
        'file_name' => 'public/docs/senhas/sem-ano.pdf',
    ]);
    $invalido = DadosGeraDoc::create([
        'tipo' => 'tag_senha',
        'content' => ['participante_id' => $inscrito->id, 'ano_referencia' => 2024],
        'file_name' => 'docs/senhas/999/alheio.pdf',
    ]);
    $legado = DadosGeraDoc::create([
        'tipo' => 'tag_senha',
        'content' => ['participante_id' => $inscrito->id, 'ano_referencia' => 2024],
        'file_name' => 'docs/senhas/plano.pdf',
    ]);
    $disco->put('docs/senhas/999/alheio.pdf', 'PDF de outra carta');
    $disco->put('docs/senhas/plano.pdf', 'PDF legado');
    $disco->put('docs/senhas/sem-ano.pdf', 'PDF legado sem ano');

    $this->artisan('interlab:preencher-ano-cartas-senha')
        ->expectsOutputToContain("Carta {$semInscrito->id}: removida (inscrição não encontrada).")
        ->expectsOutputToContain("Carta {$semAno->id}: Agenda sem ano de referência.")
        ->expectsOutputToContain("Carta {$invalido->id}: Caminho de PDF inválido")
        ->assertFailed();

    $disco->assertExists('docs/senhas/999/alheio.pdf');
    $disco->assertMissing(['docs/senhas/plano.pdf', 'docs/senhas/sem-ano.pdf']);
    expect($invalido->fresh()->getRawOriginal('file_name'))->toBe('docs/senhas/999/alheio.pdf')
        ->and($semInscrito->fresh())->toBeNull()
        ->and($semAno->fresh()->getRawOriginal('file_name'))->toBeNull()
        ->and($legado->fresh()->getRawOriginal('file_name'))->toBeNull()
        ->and($legado->fresh()->content['ano_referencia'])->toBe(2024);
});

it('simula e remove cartas sem inscrição mesmo com ano preenchido', function (string $tipo) {
    Storage::fake('public');
    $carta = DadosGeraDoc::create([
        'tipo' => $tipo,
        'content' => ['participante_id' => 9999999, 'ano_referencia' => 2026],
    ]);

    $this->artisan('interlab:preencher-ano-cartas-senha', ['--dry-run' => true])
        ->expectsOutputToContain('Cartas removidas: 1')
        ->assertSuccessful();
    expect($carta->fresh())->not->toBeNull();

    $this->artisan('interlab:preencher-ano-cartas-senha')
        ->expectsOutputToContain('Cartas removidas: 1')
        ->assertSuccessful();
    expect($carta->fresh())->toBeNull();

    $this->artisan('interlab:preencher-ano-cartas-senha')
        ->expectsOutputToContain('Cartas removidas: 0')
        ->assertSuccessful();
})->with(['tag_senha', 'tag_senha_analista']);

it('remove apenas PDFs vencidos da pasta das cartas e limpa referências correspondentes', function () {
    $this->freezeTime();
    $disco = Storage::fake('public');
    $carta = DadosGeraDoc::create(['tipo' => 'tag_senha', 'content' => [], 'file_name' => 'public/docs/senhas/legado.pdf']);
    $recente = "docs/senhas/{$carta->id}/recente.pdf";

    foreach (['docs/senhas/legado.pdf', 'docs/senhas/123/orfao.pdf', 'docs/senhas/outro.txt', 'docs/certificados/velho.pdf', 'docs/senhas/limite.pdf', $recente] as $path) {
        $disco->put($path, 'arquivo');
        touch($disco->path($path), now()->subDays(match ($path) {
            $recente => 14,
            'docs/senhas/limite.pdf' => 15,
            default => 16,
        })->getTimestamp());
    }

    $this->artisan('interlab:limpar-pdfs-cartas-senha', ['--dry-run' => true])->assertSuccessful();
    $disco->assertExists('docs/senhas/legado.pdf');
    expect($carta->fresh()->getRawOriginal('file_name'))->toBe('public/docs/senhas/legado.pdf');

    $this->artisan('interlab:limpar-pdfs-cartas-senha')->assertSuccessful();
    $disco->assertMissing(['docs/senhas/legado.pdf', 'docs/senhas/123/orfao.pdf']);
    $disco->assertExists(['docs/senhas/outro.txt', 'docs/certificados/velho.pdf', 'docs/senhas/limite.pdf', $recente]);
    expect($carta->fresh()->getRawOriginal('file_name'))->toBeNull();
});

it('regenera um PDF legado e reutiliza apenas o arquivo da própria carta', function (string $tipo, bool $emSubpasta) {
    $disco = Storage::fake('public');
    Storage::fake('local');
    config(['filesystems.default' => 'local']);
    $carta = DadosGeraDoc::create([
        'tipo' => $tipo,
        'file_name' => 'public/docs/senhas/legado.pdf',
        'content' => [
            'tag_senha' => 'cAR_469',
            'interlab_nome' => 'PEP Teste',
            'ano_referencia' => 2026,
            'empresa_nome_razao' => 'Empresa',
            'empresa_cpf_cnpj' => '000',
            'laboratorio_nome' => 'Laboratório',
            'analista_nome' => 'Analista',
            'informacoes_inscricao' => null,
        ],
    ]);
    $caminhoLegado = $emSubpasta ? "docs/senhas/{$carta->id}/antigo.pdf" : 'docs/senhas/legado.pdf';
    $carta->update(['file_name' => $emSubpasta ? $caminhoLegado : 'public/'.$caminhoLegado]);
    $disco->put($caminhoLegado, 'PDF legado');
    $fake = new class extends FakePdfBuilder
    {
        public int $geracoes = 0;

        public function save(string $path): self
        {
            $this->geracoes++;
            file_put_contents($path, '%PDF-1.4');

            return parent::save($path);
        }
    };
    Pdf::swap($fake);

    $url = route('dados-doc.download', $carta->link);
    $this->get($url)->assertOk();
    $caminho = $carta->fresh()->getRawOriginal('file_name');
    expect($caminho)->toStartWith('docs/senhas/')
        ->toEndWith("_{$carta->id}.pdf")
        ->and(dirname($caminho))->toBe('docs/senhas');
    $disco->assertExists($caminho);
    Storage::disk('local')->assertMissing($caminho);

    $view = $tipo === 'tag_senha' ? 'certificados.tag-senha' : 'certificados.tag-senha-analista';
    expect(view($view, ['dadosDoc' => $carta])->render())
        ->toContain('PEP Teste - 2026', '>CAR_469</span>')
        ->not->toContain('cAR_469');

    $this->get($url)->assertDownload(basename($caminho));
    expect($fake->geracoes)->toBe(1);
    $disco->delete($caminho);
    $this->get($url)->assertOk();
    expect($fake->geracoes)->toBe(2);
})->with([
    ['tag_senha', false],
    ['tag_senha', true],
    ['tag_senha_analista', false],
    ['tag_senha_analista', true],
]);
