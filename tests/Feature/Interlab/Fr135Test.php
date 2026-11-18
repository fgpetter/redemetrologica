<?php

use App\Models\Fornecedor;
use App\Models\FornecedorAvaliacao;
use App\Models\InterlabDespesaLancamento;
use App\Models\User;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\PessoaFactory;
use PhpOffice\PhpSpreadsheet\IOFactory;

function fr135Planilha($resposta)
{
    $arquivo = tempnam(sys_get_temp_dir(), 'fr135');
    file_put_contents($arquivo, $resposta->streamedContent());
    try {
        return IOFactory::load($arquivo);
    } finally {
        unlink($arquivo);
    }
}

test('exporta compras agrupadas por ano com notas por compra e dados literais', function () {
    $usuario = User::factory()->create();
    $usuario->givePermission('funcionario');
    $agenda = AgendaInterlabFactory::new()->create(['ano_referencia' => 2025]);
    $agenda->interlab->update(['nome' => 'PEP em Série Sólidos']);
    $outra = AgendaInterlabFactory::new()->create(['ano_referencia' => 2025, 'status' => 'CONCLUIDO']);
    $fora = AgendaInterlabFactory::new()->create(['ano_referencia' => 2024]);
    $fornecedor = Fornecedor::create(['pessoa_id' => PessoaFactory::new()->create(['nome_razao' => '=Fornecedor'])->id]);
    foreach ([[$agenda, 6], [$outra, 8]] as [$a, $nota]) {
        $compra = InterlabDespesaLancamento::create(['agenda_interlab_id' => $a->id, 'fornecedor_id' => $fornecedor->id, 'tipo' => 'produto', 'especificacao' => $nota === 6 ? "\u{00a0}Padrão  TESTE\u{00a0}" : 'padrão teste', 'data_compra' => '2025-02-01']);
        $compra->itens()->createMany(array_fill(0, $nota === 6 ? 3 : 1, ['material_servico' => '=Item', 'nota_fiscal' => '0', 'lote' => '0', 'data_compra' => '2024-01-01']));
        FornecedorAvaliacao::create(['interlab_despesa_lancamento_id' => $compra->id, 'fornecedor_id' => $fornecedor->id, 'custo' => $nota, 'qualidade' => $nota, 'tempo' => $nota, 'avaliador_user_id' => $usuario->id]);
    }
    InterlabDespesaLancamento::create(['agenda_interlab_id' => $agenda->id, 'fornecedor_id' => $fornecedor->id, 'tipo' => 'servico', 'especificacao' => 'Segundo grupo']);
    InterlabDespesaLancamento::create(['agenda_interlab_id' => $agenda->id]);
    InterlabDespesaLancamento::create(['agenda_interlab_id' => $fora->id, 'fornecedor_id' => $fornecedor->id, 'tipo' => 'produto', 'especificacao' => 'Outro']);
    $aba = fr135Planilha($this->actingAs($usuario)->get(route('agenda-interlab-fr135-anual', ['ano' => 2025]))->assertDownload('FR-135-2025.xlsx'))->getActiveSheet();
    expect($aba->getCell('J9')->getValue())->toEqual(7)
        ->and($aba->getCell('K9')->getValue())->toBe('Reprovado')
        ->and($aba->getCell('E9')->getValue())->toBe('=Fornecedor')
        ->and($aba->getCell('E9')->getDataType())->toBe('s')
        ->and($aba->getCell('L9')->getValue())->toContain('Nota fiscal: 0', 'Lote: 0', '01/02/2025')
        ->and($aba->getCell('L10')->getValue())->toContain('=Item')
        ->and($aba->getCell('L10')->getDataType())->toBe('s')
        ->and($aba->getCell('B10')->getValue())->toBeNull()
        ->and($aba->getCell('J10')->getValue())->toBeNull()
        ->and($aba->getCell('B13')->getValue())->toBe('2')
        ->and($aba->getCell('D13')->getValue())->toBe('Serviço')
        ->and($aba->getCell('B6')->getValue())->toContain('1')
        ->and($aba->getCell('C9')->getValue())->toContain($agenda->interlab->nome, $outra->interlab->nome);
    $abaAgenda = fr135Planilha($this->get(route('agenda-interlab-fr135-agenda', $agenda->uid))->assertDownload('FR135-PEP-EM-SERIE-SOLIDOS-2025.xlsx'))->getActiveSheet();
    expect($abaAgenda->getCell('J9')->getValue())->toEqual(6);
    $abaConcluida = fr135Planilha($this->get(route('agenda-interlab-fr135-agenda', $outra->uid))->assertOk())->getActiveSheet();
    expect($abaConcluida->getCell('K9')->getValue())->toBe('Aprovado');
});

test('mostra pendência e exige autenticação e ano de referência', function () {
    $agenda = AgendaInterlabFactory::new()->create(['ano_referencia' => 2025]);
    $fornecedor = Fornecedor::create(['pessoa_id' => PessoaFactory::new()->create()->id]);
    InterlabDespesaLancamento::create(['agenda_interlab_id' => $agenda->id, 'fornecedor_id' => $fornecedor->id, 'tipo' => 'servico', 'especificacao' => 'Análise']);
    $this->get(route('agenda-interlab-fr135-agenda', $agenda->uid))->assertRedirect();
    $usuario = User::factory()->create();
    $usuario->givePermission('funcionario');
    $aba = fr135Planilha($this->actingAs($usuario)->get(route('agenda-interlab-fr135-agenda', $agenda->uid))->assertOk())->getActiveSheet();
    expect($aba->getCell('K9')->getValue())->toBe('Pendente')->and($aba->getCell('J9')->getValue())->toBeNull()->and($aba->getCell('L9')->getValue())->toContain('1');
    $agenda->update(['ano_referencia' => null]);
    $this->get(route('agenda-interlab-fr135-agenda', $agenda->uid))->assertRedirect()->assertSessionHas('warning');
});

test('mantém médias das compras avaliadas quando outra está pendente e restringe acesso', function () {
    $agenda = AgendaInterlabFactory::new()->create(['ano_referencia' => 2025]);
    $fornecedor = Fornecedor::create(['pessoa_id' => PessoaFactory::new()->create()->id]);
    $dados = ['agenda_interlab_id' => $agenda->id, 'fornecedor_id' => $fornecedor->id, 'tipo' => 'produto', 'especificacao' => 'Teste'];
    $compra = InterlabDespesaLancamento::create($dados);
    InterlabDespesaLancamento::create($dados);
    FornecedorAvaliacao::create(['interlab_despesa_lancamento_id' => $compra->id, 'fornecedor_id' => $fornecedor->id, 'custo' => 9, 'qualidade' => 8, 'tempo' => 10]);
    $semPermissao = User::factory()->create();
    $this->actingAs($semPermissao)->get(route('agenda-interlab-fr135-anual', ['ano' => 2025]))->assertNotFound();
    $usuario = User::factory()->create();
    $usuario->givePermission('funcionario');
    $aba = fr135Planilha($this->actingAs($usuario)->get(route('agenda-interlab-fr135-anual', ['ano' => 2025]))->assertOk())->getActiveSheet();
    expect($aba->getCell('J9')->getValue())->toEqual(9)
        ->and($aba->getCell('K9')->getValue())->toBe('Pendente')
        ->and($aba->getCell('M9')->getValue())->toBe('Não informado')
        ->and($aba->getCell('L9')->getValue())->toContain('1');
});

test('gera cabeçalho e mensagem para período sem compras', function () {
    $usuario = User::factory()->create();
    $usuario->givePermission('admin');
    $aba = fr135Planilha($this->actingAs($usuario)->get(route('agenda-interlab-fr135-anual', ['ano' => 2025]))->assertOk())->getActiveSheet();
    expect($aba->getCell('B7')->getValue())->toBe('ID')
        ->and($aba->getCell('B9')->getValue())->toContain('Nenhuma compra');
});
