<?php

namespace App\Enums;

enum SuporteStatus: string
{
    case Aberta = 'aberta';
    case EmAnalise = 'em_analise';
    case EmAndamento = 'em_andamento';
    case AguardandoRetorno = 'aguardando_retorno';
    case Concluida = 'concluida';
    case Cancelada = 'cancelada';

    public function label(): string
    {
        return match ($this) {
            self::Aberta => 'Aberta',
            self::EmAnalise => 'Em análise',
            self::EmAndamento => 'Em andamento',
            self::AguardandoRetorno => 'Aguardando retorno',
            self::Concluida => 'Concluída',
            self::Cancelada => 'Cancelada',
        };
    }

    public function encerrada(): bool
    {
        return in_array($this, [self::Concluida, self::Cancelada], true);
    }

    public function cor(): string
    {
        return match ($this) {
            self::Aberta => 'primary',
            self::EmAnalise => 'info',
            self::EmAndamento => 'warning',
            self::AguardandoRetorno => 'secondary',
            self::Concluida => 'success',
            self::Cancelada => 'dark',
        };
    }
}
