<?php

namespace App\Actions\Interlab;

class ResultadoEtapaLotePostagem
{
    private function __construct(
        private string $estado,
        public int $atrasoSegundos = 0,
    ) {}

    public static function concluida(): self
    {
        return new self('concluida');
    }

    public static function retentar(int $atrasoSegundos): self
    {
        return new self('retentar', max(1, $atrasoSegundos));
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
