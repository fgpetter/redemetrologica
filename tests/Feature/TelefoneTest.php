<?php

use App\Helpers\Telefone;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Models\FornecedorArea;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\Laboratorio;
use App\Models\LaboratorioInterno;
use App\Models\NotaFiscalAvulsa;
use App\Models\Pessoa;
use Illuminate\Support\Facades\Validator;

it('valida telefones fixos e celulares mascarados ou apenas com dígitos pelo pacote', function (string $telefone): void {
    $telefonePreparado = Telefone::paraValidacao($telefone);
    $validator = Validator::make(['telefone' => $telefonePreparado], [
        'telefone' => ['required', 'celular_com_ddd'],
    ]);

    expect($validator->errors()->all())->toBe([]);
})->with([
    'fixo sem máscara' => '1133334444',
    'fixo com máscara' => '(11) 3333-4444',
    'celular sem máscara' => '11988887777',
    'celular com máscara' => '(11) 98888-7777',
]);

it('rejeita entradas incompletas, excessivas, com letras ou código de país', function (string $telefone): void {
    $validator = Validator::make(['telefone' => Telefone::paraValidacao($telefone)], [
        'telefone' => ['required', 'celular_com_ddd'],
    ]);

    expect($validator->fails())->toBeTrue();
})->with([
    'incompleta' => '(11) 9888-777',
    'dígitos excedentes' => '119888877777',
    'letras' => '(11) 98888-7777abc',
    'código de país' => '+55 (11) 98888-7777',
]);

it('mantém campos opcionais vazios e rejeita valores vazios obrigatórios', function (): void {
    expect(Validator::make(['telefone' => null], ['telefone' => ['nullable', 'celular_com_ddd']])->fails())->toBeFalse()
        ->and(Validator::make(['telefone' => null], ['telefone' => ['required', 'celular_com_ddd']])->fails())->toBeTrue();
});

it('persiste telefone somente com dígitos nos modelos que guardam contato', function (): void {
    $modelos = [
        Pessoa::make(['telefone' => '(11) 98888-7777', 'telefone_alt' => '(11) 3333-4444', 'celular' => '11988887777']),
        Laboratorio::make(['telefone' => '(11) 3333-4444']),
        LaboratorioInterno::make(['telefone' => '(11) 98888-7777']),
        AgendaCursos::make(['contato_telefone' => '(11) 3333-4444']),
        CursoInscrito::make(['telefone' => '(11) 98888-7777']),
        FornecedorArea::make(['pessoa_contato_telefone' => '(11) 3333-4444']),
        InterlabInscrito::make(['telefone' => '(11) 98888-7777']),
        InterlabAnalista::make(['telefone' => '(11) 3333-4444']),
    ];

    expect($modelos[0]->getAttributes())
        ->toMatchArray(['telefone' => '11988887777', 'telefone_alt' => '1133334444', 'celular' => '11988887777'])
        ->and($modelos[1]->getAttributes()['telefone'])->toBe('1133334444')
        ->and($modelos[2]->getAttributes()['telefone'])->toBe('11988887777')
        ->and($modelos[3]->getAttributes()['contato_telefone'])->toBe('1133334444')
        ->and($modelos[4]->getAttributes()['telefone'])->toBe('11988887777')
        ->and($modelos[5]->getAttributes()['pessoa_contato_telefone'])->toBe('1133334444')
        ->and($modelos[6]->getAttributes()['telefone'])->toBe('11988887777')
        ->and($modelos[7]->getAttributes()['telefone'])->toBe('1133334444');
});

it('apresenta telefones formatados em todos os models e conserva dígitos fora dos inputs', function (): void {
    $telefones = [
        [Pessoa::make(['telefone' => '11988887777']), 'telefone', '(11) 98888-7777'],
        [Pessoa::make(['telefone_alt' => '1133334444']), 'telefone_alt', '(11) 3333-4444'],
        [Pessoa::make(['celular' => '11988887777']), 'celular', '(11) 98888-7777'],
        [Laboratorio::make(['telefone' => '1133334444']), 'telefone', '(11) 3333-4444'],
        [LaboratorioInterno::make(['telefone' => '11988887777']), 'telefone', '(11) 98888-7777'],
        [AgendaCursos::make(['contato_telefone' => '1133334444']), 'contato_telefone', '(11) 3333-4444'],
        [CursoInscrito::make(['telefone' => '11988887777']), 'telefone', '(11) 98888-7777'],
        [FornecedorArea::make(['pessoa_contato_telefone' => '1133334444']), 'pessoa_contato_telefone', '(11) 3333-4444'],
        [InterlabInscrito::make(['telefone' => '11988887777']), 'telefone', '(11) 98888-7777'],
        [InterlabAnalista::make(['telefone' => '1133334444']), 'telefone', '(11) 3333-4444'],
        [NotaFiscalAvulsa::make(['telefone' => '11988887777']), 'telefone', '(11) 98888-7777'],
    ];

    foreach ($telefones as [$model, $campo, $formatado]) {
        expect($model->{$campo})->toBe($formatado)
            ->and($model->getAttributes()[$campo])->toBe(Telefone::somenteNumeros($formatado))
            ->and($model->toArray()[$campo])->toBe($formatado);
    }

    $telefoneInvalido = Pessoa::make();
    $telefoneInvalido->setRawAttributes(['telefone' => '+55 (11) 98888-7777']);

    expect($telefoneInvalido->telefone)->toBe('5511988887777');

    $telefoneNulo = Pessoa::make();
    $telefoneNulo->setRawAttributes(['telefone' => null]);

    expect($telefoneNulo->telefone)->toBeNull();
});
