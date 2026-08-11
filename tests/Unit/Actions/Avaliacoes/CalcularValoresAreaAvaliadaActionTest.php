<?php

use App\Actions\Avaliacoes\CalcularValoresAreaAvaliadaAction;

test('calcula valores derivados da área com dias fracionários', function () {
    $valores = app(CalcularValoresAreaAvaliadaAction::class)->execute([
        'dias' => 1.5,
        'valor_dia' => '1.000,00',
        'valor_lider' => '200,00',
        'valor_estim_desloc' => '50,00',
        'valor_estim_alim' => '30,00',
        'valor_estim_hosped' => '20,00',
        'valor_estim_extras' => '10,00',
        'valor_real_desloc' => '40,00',
        'valor_real_alim' => '25,00',
        'valor_real_hosped' => '15,00',
        'valor_real_extras' => '5,00',
    ]);

    expect($valores['valor_avaliador'])->toBe(1700.0)
        ->and($valores['total_gastos_estim'])->toBe(110.0)
        ->and($valores['total_gastos_reais'])->toBe(85.0);
});

test('trata valores nulos como zero no cálculo da área', function () {
    $valores = app(CalcularValoresAreaAvaliadaAction::class)->execute([
        'dias' => null,
        'valor_dia' => null,
        'valor_lider' => null,
    ]);

    expect($valores['valor_avaliador'])->toBe(0.0)
        ->and($valores['total_gastos_estim'])->toBe(0.0)
        ->and($valores['total_gastos_reais'])->toBe(0.0);
});
