<?php

namespace Database\Factories;

use App\Models\SuporteArquivo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<SuporteArquivo> */
class SuporteArquivoFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'uid' => (string) Str::uuid(),
            'demanda_id' => SuporteDemandaFactory::new(),
            'user_id' => UserFactory::new(),
            'tipo' => 'anexo',
            'nome' => 'exemplo.pdf',
            'caminho' => 'suporte/arquivos/'.Str::uuid().'.pdf',
            'mime' => 'application/pdf',
            'tamanho' => 100,
        ];
    }
}
