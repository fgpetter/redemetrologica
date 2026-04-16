<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InterlabInscrito>
 */
class InterlabInscritoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pessoa_id' => PessoaFactory::new(),
            'empresa_id' => PessoaFactory::new(),
            'laboratorio_id' => null,
            'pessoa_inscrito_id' => null,
            'agenda_interlab_id' => AgendaInterlabFactory::new(),
            'data_inscricao' => now(),
            'valor' => 0,
            'pesquisa_id' => null,
            'resposta_pesquisa' => null,
            'certificado_emitido' => null,
            'certificado_path' => null,
            'informacoes_inscricao' => null,
            'responsavel_tecnico' => null,
            'telefone' => null,
            'email' => null,
            'tag_senha' => null,
            'lancamento_financeiro_id' => null,
        ];
    }
}
