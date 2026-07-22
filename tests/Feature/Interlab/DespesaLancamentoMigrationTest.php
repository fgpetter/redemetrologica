<?php

use App\Models\AgendaInterlab;
use App\Models\Fornecedor;
use App\Models\FornecedorAvaliacao;
use App\Models\Interlab;
use App\Models\InterlabDespesa;
use App\Models\InterlabDespesaLancamento;
use Database\Factories\PessoaFactory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('migra despesas antigas para lancamentos com itens e avaliacao', function () {
    Artisan::call('migrate:rollback', ['--step' => 1]);

    expect(Schema::hasTable('interlab_despesa_lancamentos'))->toBeFalse()
        ->and(Schema::hasColumn('interlab_despesas', 'agenda_interlab_id'))->toBeTrue()
        ->and(Schema::hasColumn('fornecedores_avaliacao', 'agenda_interlab_id'))->toBeTrue();

    $interlab = Interlab::query()->create([
        'nome' => 'Interlab Migração',
        'tipo' => 'INTERLABORATORIAL',
    ]);

    $agenda = AgendaInterlab::query()->create([
        'interlab_id' => $interlab->id,
        'status' => 'CONFIRMADO',
        'inscricao' => true,
        'ano_referencia' => 2026,
    ]);

    $pessoa = PessoaFactory::new()->create(['nome_razao' => 'Fornecedor Legacy']);
    $fornecedor = Fornecedor::query()->create(['pessoa_id' => $pessoa->id]);

    $now = now();

    $despesaUmId = DB::table('interlab_despesas')->insertGetId([
        'uid' => Str::lower(Str::random(13)),
        'agenda_interlab_id' => $agenda->id,
        'fornecedor_id' => $fornecedor->id,
        'material_servico' => 'Produto 1',
        'quantidade' => 2,
        'valor' => 10,
        'total' => 20,
        'data_compra' => '2026-06-01',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $despesaDoisId = DB::table('interlab_despesas')->insertGetId([
        'uid' => Str::lower(Str::random(13)),
        'agenda_interlab_id' => $agenda->id,
        'fornecedor_id' => $fornecedor->id,
        'material_servico' => 'Produto 2',
        'quantidade' => 1,
        'valor' => 5,
        'total' => 5,
        'data_compra' => '2026-06-02',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('fornecedores_avaliacao')->insert([
        'uid' => Str::lower(Str::random(13)),
        'agenda_interlab_id' => $agenda->id,
        'fornecedor_id' => $fornecedor->id,
        'custo' => 3,
        'tempo' => 4,
        'qualidade' => 5,
        'media' => 4,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    Artisan::call('migrate');

    expect(Schema::hasTable('interlab_despesa_lancamentos'))->toBeTrue()
        ->and(Schema::hasColumn('interlab_despesas', 'agenda_interlab_id'))->toBeFalse()
        ->and(Schema::hasColumn('fornecedores_avaliacao', 'agenda_interlab_id'))->toBeFalse()
        ->and(InterlabDespesaLancamento::query()->count())->toBe(1);

    $lancamento = InterlabDespesaLancamento::query()->first();

    expect($lancamento->agenda_interlab_id)->toBe($agenda->id)
        ->and($lancamento->fornecedor_id)->toBe($fornecedor->id)
        ->and(InterlabDespesa::query()->whereIn('id', [$despesaUmId, $despesaDoisId])->count())->toBe(2)
        ->and(
            InterlabDespesa::query()
                ->where('interlab_despesa_lancamento_id', $lancamento->id)
                ->count()
        )->toBe(2);

    $avaliacao = FornecedorAvaliacao::query()->first();

    expect($avaliacao)->not->toBeNull()
        ->and($avaliacao->interlab_despesa_lancamento_id)->toBe($lancamento->id)
        ->and($avaliacao->fornecedor_id)->toBe($fornecedor->id)
        ->and((float) $avaliacao->media)->toBe(4.0);
});
