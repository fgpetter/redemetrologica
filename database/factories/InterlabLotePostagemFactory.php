<?php

namespace Database\Factories;

use App\Enums\CorreiosFormatoObjeto;
use App\Enums\InterlabLotePostagemStatus;
use App\Models\InterlabLotePostagem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InterlabLotePostagem>
 */
class InterlabLotePostagemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agenda_interlab_id' => AgendaInterlabFactory::new(),
            'nome' => 'Lote setembro',
            'codigo_formato' => CorreiosFormatoObjeto::CaixaPacote,
            'altura' => '10',
            'largura' => '15',
            'comprimento' => '20',
            'diametro' => null,
            'peso_gramas' => 500,
            'declaracao_conteudo' => [[
                'conteudo' => 'Amostra para ensaio interlaboratorial',
                'quantidade' => '1',
                'valor' => '50.00',
            ]],
            'status' => InterlabLotePostagemStatus::ClassificandoServico,
            'ultima_progressao_em' => now(),
        ];
    }
}
