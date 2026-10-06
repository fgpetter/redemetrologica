<?php

namespace App\Enums;

enum InterlabLotePostagemStatus: string
{
    case ClassificandoServico = 'classificando_servico';
    case GerandoPrePostagens = 'gerando_pre_postagens';
    case GerandoEtiquetas = 'gerando_etiquetas';
    case GerandoDeclaracao = 'gerando_declaracao';
    case Concluido = 'concluido';
    case Erro = 'erro';

    public function label(): string
    {
        return match ($this) {
            self::ClassificandoServico => 'Classificando serviço',
            self::GerandoPrePostagens => 'Gerando pré-postagens',
            self::GerandoEtiquetas => 'Gerando etiquetas',
            self::GerandoDeclaracao => 'Gerando DACE',
            self::Concluido => 'Concluído',
            self::Erro => 'Erro',
        };
    }

    public function emProcessamento(): bool
    {
        return ! in_array($this, [self::Concluido, self::Erro], true);
    }
}
