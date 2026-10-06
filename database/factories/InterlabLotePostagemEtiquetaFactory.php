<?php

namespace Database\Factories;

use App\Enums\InterlabLotePostagemEtiquetaStatus;
use App\Models\InterlabLotePostagemEtiqueta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InterlabLotePostagemEtiqueta>
 */
class InterlabLotePostagemEtiquetaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'interlab_lote_postagem_id' => InterlabLotePostagemFactory::new(),
            'status' => InterlabLotePostagemEtiquetaStatus::Pendente,
        ];
    }

    public function pendente(): static
    {
        return $this->state(fn (): array => [
            'status' => InterlabLotePostagemEtiquetaStatus::Pendente,
            'solicitacao_iniciada_em' => null,
            'id_recibo_rotulo' => null,
            'etiqueta_path' => null,
        ]);
    }

    public function solicitada(): static
    {
        return $this->state(fn (): array => [
            'status' => InterlabLotePostagemEtiquetaStatus::Solicitada,
            'solicitacao_iniciada_em' => now(),
            'id_recibo_rotulo' => null,
            'etiqueta_path' => null,
        ]);
    }

    public function gerada(): static
    {
        return $this->state(fn (): array => [
            'status' => InterlabLotePostagemEtiquetaStatus::Gerada,
            'solicitacao_iniciada_em' => now()->subMinute(),
            'id_recibo_rotulo' => 'recibo-teste',
            'etiqueta_path' => 'correios/etiquetas/teste.pdf',
        ]);
    }
}
