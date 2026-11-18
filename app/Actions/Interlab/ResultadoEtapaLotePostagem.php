<?php

namespace App\Actions\Interlab;

class ResultadoEtapaLotePostagem
{
    private function __construct(
        private string $estado,
        public int $atrasoSegundos = 0,
        public string $motivo = 'nenhum',
    ) {}

    public static function concluida(): self
    {
        return new self('concluida');
    }

    public static function retentar(int $atrasoSegundos, string $motivo = 'falha_transitoria'): self
    {
        return new self('retentar', max(1, $atrasoSegundos), $motivo);
    }

    public static function falhou(): self
    {
        return new self('falhou');
    }

    public function estaConcluida(): bool
    {
        return $this->estado === 'concluida';
    }

    public function deveRetentar(): bool
    {
        return $this->estado === 'retentar';
    }

    public function estaFalhou(): bool
    {
        return $this->estado === 'falhou';
    }
}
