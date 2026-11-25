<?php

namespace App\Actions\Interlab;

use App\Integrations\Correios\CorreiosBuscaCepClient;
use App\Integrations\Correios\CorreiosLog;
use Illuminate\Validation\ValidationException;

class ValidarCepsDestinatariosInterlabAction
{
    public function __construct(private CorreiosBuscaCepClient $buscaCep) {}

    /** @param array<int, array{nome: string, endereco: array{cep: string, ...}}> $destinatarios */
    public function execute(array $destinatarios, string $tentativaId): void
    {
        $inicio = hrtime(true);
        $laboratoriosPorCep = [];

        foreach ($destinatarios as $destinatario) {
            $laboratoriosPorCep[$destinatario['endereco']['cep']][] = $destinatario['nome'];
        }

        $ceps = array_map(strval(...), array_keys($laboratoriosPorCep));
        $consulta = $this->buscaCep->consultar($ceps, $tentativaId);
        $confirmados = 0;
        $recusados = 0;
        $semConfirmacao = 0;
        $mensagens = [];

        foreach ($ceps as $cep) {
            $resultado = $consulta['resultados'][$cep];

            if ($resultado === true) {
                $confirmados++;
            } elseif ($resultado === null) {
                $semConfirmacao++;
            } else {
                $recusados++;
                $cepFormatado = substr($cep, 0, 5).'-'.substr($cep, 5);

                foreach ($laboratoriosPorCep[$cep] as $laboratorio) {
                    $mensagens['cepDestinatarios'][] = "O CEP {$cepFormatado} do laboratório {$laboratorio} não foi encontrado pelos Correios.";
                }
            }
        }

        CorreiosLog::info('cep.resumo', [
            'tentativa_id' => $tentativaId,
            'ceps_consultados' => $consulta['consultados'],
            'ceps_confirmados' => $confirmados,
            'ceps_recusados' => $recusados,
            'ceps_sem_confirmacao' => $semConfirmacao,
            'duracao_ms' => (int) ((hrtime(true) - $inicio) / 1_000_000),
        ]);

        if ($semConfirmacao > 0) {
            $mensagens['consultaCep'] = 'A validação de CEPs ficou incompleta. Tente novamente.';
        }

        if ($mensagens !== []) {
            throw ValidationException::withMessages($mensagens);
        }
    }
}
