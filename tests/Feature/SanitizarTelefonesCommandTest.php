<?php

use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('sanitiza os telefones em todas as colunas mapeadas e preserva valores nulos', function (): void {
    $pessoa = Pessoa::query()->create([
        'nome_razao' => 'Pessoa de Teste',
        'cpf_cnpj' => '12345678901',
        'tipo_pessoa' => 'PF',
        'telefone' => '(11) 3333-4444',
        'telefone_alt' => null,
        'celular' => 'ramal',
    ]);

    DB::table('pessoas')->where('id', $pessoa->id)->update([
        'telefone' => '(11) 3333-4444',
        'telefone_alt' => null,
        'celular' => 'ramal',
    ]);

    DB::table('notas_fiscais_avulsas')->insert([
        'uid' => (string) Str::uuid(),
        'cpf_cnpj' => '12345678901',
        'rg_ie' => '123',
        'insc_munic' => '123',
        'nome_razao' => 'Pessoa de Teste',
        'telefone' => '+55 (11) 99999-8888',
        'email' => 'teste@example.com',
        'modalidade' => 'BOLETO',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Artisan::call('telefones:sanitizar');

    expect(DB::table('pessoas')->where('id', $pessoa->id)->first())
        ->toMatchObject([
            'telefone' => '1133334444',
            'telefone_alt' => null,
            'celular' => '',
        ])
        ->and(DB::table('notas_fiscais_avulsas')->value('telefone'))->toBe('5511999998888');
});

it('oferece simulação sem alterar os telefones armazenados', function (): void {
    $pessoa = Pessoa::query()->create([
        'nome_razao' => 'Pessoa de Simulação',
        'cpf_cnpj' => '98765432100',
        'tipo_pessoa' => 'PF',
    ]);

    DB::table('pessoas')->where('id', $pessoa->id)->update([
        'telefone' => '(11) 3333-4444',
    ]);

    Artisan::call('telefones:sanitizar', ['--dry-run' => true]);

    expect(DB::table('pessoas')->where('id', $pessoa->id)->value('telefone'))->toBe('(11) 3333-4444')
        ->and(Artisan::output())->toContain('seriam sanitizados');
});
