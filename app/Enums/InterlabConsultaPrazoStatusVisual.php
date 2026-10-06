<?php

namespace App\Enums;

enum InterlabConsultaPrazoStatusVisual: string
{
    case AguardandoConsulta = 'aguardando_consulta';
    case Consultando = 'consultando';
    case AguardandoTentativa = 'aguardando_tentativa';
    case Sedex12 = 'sedex_12';
    case Sedex = 'sedex';
    case CriandoPrePostagem = 'criando_pre_postagem';
    case Prepostado = 'prepostado';
    case GerandoEtiqueta = 'gerando_etiqueta';
    case EtiquetaGerada = 'etiqueta_gerada';
    case GerandoDeclaracao = 'gerando_declaracao';
    case Entregue = 'entregue';
    case Intervencao = 'intervencao';

    public function label(): string
    {
        return match ($this) {
            self::AguardandoConsulta => 'Aguardando consulta de prazo',
            self::Consultando => 'Consultando prazo',
            self::AguardandoTentativa => 'Aguardando nova tentativa',
            self::Sedex12 => 'Prazo validado — SEDEX 12',
            self::Sedex => 'Prazo validado — SEDEX',
            self::CriandoPrePostagem => 'Criando pré-postagem',
            self::Prepostado => 'Pré-postado',
            self::GerandoEtiqueta => 'Gerando etiqueta',
            self::EtiquetaGerada => 'Etiqueta gerada',
            self::GerandoDeclaracao => 'Gerando DACE',
            self::Entregue => 'Entregue',
            self::Intervencao => 'Requer intervenção',
        };
    }
}
