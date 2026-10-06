<?php

namespace Database\Factories;

use App\Enums\CorreiosPrePostagemStatus;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Models\Endereco;
use App\Models\InterlabLaboratorio;
use App\Models\InterlabLotePostagemItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<InterlabLotePostagemItem>
 */
class InterlabLotePostagemItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $empresa = PessoaFactory::new()->create(['tipo_pessoa' => 'PJ']);
        $endereco = Endereco::query()->create([
            'pessoa_id' => $empresa->id,
            'endereco' => 'Praça da Sé, 1',
            'bairro' => 'Sé',
            'cep' => '01001000',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
        ]);
        $laboratorio = InterlabLaboratorio::query()->create([
            'empresa_id' => $empresa->id,
            'endereco_id' => $endereco->id,
            'nome' => 'Laboratório Teste',
        ]);

        return [
            'interlab_lote_postagem_id' => InterlabLotePostagemFactory::new(),
            'interlab_inscrito_id' => InterlabInscritoFactory::new(),
            'interlab_laboratorio_id' => $laboratorio->id,
            'nu_requisicao' => (string) Str::uuid(),
            'destinatario' => [
                'nome' => 'Laboratório Teste',
                'endereco' => [
                    'cep' => '01001000',
                    'logradouro' => 'Praça da Sé',
                    'numero' => '1',
                    'bairro' => 'Sé',
                    'cidade' => 'São Paulo',
                    'uf' => 'SP',
                    'regiao' => '',
                ],
            ],
            'status' => InterlabLotePostagemItemStatus::AguardandoClassificacao,
        ];
    }

    public function classificadoSedex12(): static
    {
        return $this->state(fn (): array => [
            'status' => InterlabLotePostagemItemStatus::Classificado,
            'codigo_servico' => '03140',
            'consulta_prazo_em' => now(),
            'motivo_fallback_servico' => null,
            'consulta_prazo_request' => [
                'coProduto' => '03140',
                'nuRequisicao' => (string) Str::uuid(),
            ],
            'consulta_prazo_response' => [
                'prazoEntrega' => 1,
            ],
        ]);
    }

    public function classificadoSedex(): static
    {
        return $this->state(fn (): array => [
            'status' => InterlabLotePostagemItemStatus::Classificado,
            'codigo_servico' => '03220',
            'consulta_prazo_em' => now(),
            'motivo_fallback_servico' => 'PRZ-008',
            'consulta_prazo_request' => [
                'coProduto' => '03140',
                'nuRequisicao' => (string) Str::uuid(),
            ],
            'consulta_prazo_response' => [
                'txErro' => 'PRZ-008',
            ],
        ]);
    }

    public function prepostado(): static
    {
        return $this->state(fn (): array => [
            'status' => InterlabLotePostagemItemStatus::Prepostado,
            'codigo_servico' => '03140',
            'consulta_prazo_em' => now(),
            'id_prepostagem' => 'PP'.Str::random(20),
            'codigo_objeto' => 'DG'.fake()->numerify('#########').'BR',
            'status_atual' => CorreiosPrePostagemStatus::Prepostado,
        ]);
    }

    public function etiquetaGerada(): static
    {
        return $this->prepostado()->state(fn (): array => [
            'status' => InterlabLotePostagemItemStatus::EtiquetaGerada,
        ]);
    }

    public function entregue(): static
    {
        return $this->etiquetaGerada()->state(fn (): array => [
            'status' => InterlabLotePostagemItemStatus::Entregue,
            'status_correios' => 'BDE-01',
            'ultimo_evento' => 'Objeto entregue ao destinatário',
            'rastreado_em' => now(),
        ]);
    }
}
