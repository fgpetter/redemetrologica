<?php

use App\Actions\Avaliacoes\SalvarOrcamentoAvaliacaoAction;
use App\Models\AgendaAvaliacao;
use App\Models\Laboratorio;
use App\Models\TipoAvaliacao;
use Database\Factories\PessoaFactory;

test('persiste somente perc lucro, data de envio e observações', function () {
    $pessoa = PessoaFactory::new()->create();
    $laboratorio = Laboratorio::query()->create([
        'pessoa_id' => $pessoa->id,
        'nome_laboratorio' => 'Lab Persistência',
        'contato' => 'Contato',
        'telefone' => '11999999999',
        'email' => 'persist@example.com',
        'responsavel_tecnico' => 'Responsável',
    ]);
    $tipo = TipoAvaliacao::query()->create(['descricao' => 'Inicial']);

    $avaliacao = AgendaAvaliacao::query()->create([
        'laboratorio_id' => $laboratorio->id,
        'tipo_avaliacao_id' => $tipo->id,
        'perc_lucro' => 10,
        'observacoes_orcamento' => 'antiga',
    ]);

    app(SalvarOrcamentoAvaliacaoAction::class)->execute($avaliacao, [
        'perc_lucro' => 25,
        'data_envio_proposta' => now()->toDateString(),
        'observacoes_orcamento' => 'nova',
        'nf' => 999,
        'valor_proposta' => 9999,
        'superavit' => 111,
        'soma_avaliadores' => 222,
    ]);

    $avaliacao->refresh();

    expect((float) $avaliacao->perc_lucro)->toBe(25.0)
        ->and($avaliacao->observacoes_orcamento)->toBe('nova')
        ->and($avaliacao->data_envio_proposta)->not->toBeNull()
        ->and(array_key_exists('nf', $avaliacao->getAttributes()))->toBeFalse()
        ->and(array_key_exists('valor_proposta', $avaliacao->getAttributes()))->toBeFalse();
});
