<?php

namespace App\Enums;

enum InterlabLotePostagemItemStatus: string
{
    case AguardandoClassificacao = 'aguardando_classificacao';
    case Classificado = 'classificado';
    case Prepostado = 'prepostado';
    case EtiquetaGerada = 'etiqueta_gerada';
    case Entregue = 'entregue';
    case Erro = 'erro';

    public function label(): string
    {
        return match ($this) {
            self::AguardandoClassificacao => 'Aguardando classificação',
            self::Classificado => 'Classificado',
            self::Prepostado => 'Pré-postado',
            self::EtiquetaGerada => 'Etiqueta gerada',
            self::Entregue => 'Entregue',
            self::Erro => 'Erro',
        };
    }
}
