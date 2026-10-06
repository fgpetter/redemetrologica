<?php

namespace App\Actions\Interlab;

use App\Models\InterlabInscrito;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DestinatarioInterlabSnapshot
{
    /**
     * Destinatário no formato da pré-postagem. O snapshot gravado não é alterado.
     *
     * @param  array{nome: string, endereco: array<string, mixed>}  $destinatario
     * @return array{nome: string, endereco: array<string, mixed>}
     */
    public function paraPrePostagem(array $destinatario): array
    {
        return [
            'nome' => Str::substr($destinatario['nome'], 0, 50),
            'endereco' => $destinatario['endereco'],
        ];
    }

    /**
     * @return list<string>
     */
    public function mensagens(InterlabInscrito $inscrito): array
    {
        $normalizado = $this->normalizarAntesDeValidar($inscrito);
        $rotulo = $normalizado['nome'] !== '' ? $normalizado['nome'] : 'sem nome';
        $mensagens = [];
        $tamanhoNome = Str::length($normalizado['nome']);

        if ($tamanhoNome === 0) {
            $mensagens[] = 'Informe o nome do laboratório.';
        } elseif ($tamanhoNome < 3 || $tamanhoNome > 255) {
            $mensagens[] = "O nome do laboratório {$rotulo} deve ter entre 3 e 255 caracteres.";
        }

        if (strlen($normalizado['cep']) !== 8) {
            $mensagens[] = "O CEP do laboratório {$rotulo} deve ter 8 dígitos.";
        }

        $mensagens = [
            ...$mensagens,
            ...$this->mensagensDeLogradouro($normalizado['logradouro'], $normalizado['numero'], $rotulo),
        ];

        $bairro = $normalizado['bairro'];

        if ($bairro === '') {
            $mensagens[] = "Informe o bairro do laboratório {$rotulo}.";
        } elseif (Str::length($bairro) > 30) {
            $mensagens[] = "O bairro do laboratório {$rotulo} deve ter no máximo 30 caracteres.";
        }

        $cidade = $normalizado['cidade'];

        if ($cidade === '') {
            $mensagens[] = "Informe a cidade do laboratório {$rotulo}.";
        } elseif (Str::length($cidade) > 30) {
            $mensagens[] = "A cidade do laboratório {$rotulo} deve ter no máximo 30 caracteres.";
        }

        if (strlen($normalizado['uf']) !== 2) {
            $mensagens[] = "Informe a UF do laboratório {$rotulo}.";
        }

        return $mensagens;
    }

    /**
     * Snapshot do destinatário no formato da API dos Correios.
     *
     * @return array{
     *     nome: string,
     *     endereco: array{
     *         cep: string,
     *         logradouro: string,
     *         numero: string,
     *         complemento?: string,
     *         bairro: string,
     *         cidade: string,
     *         uf: string,
     *         regiao: string
     *     }
     * }
     */
    public function montar(InterlabInscrito $inscrito): array
    {
        $normalizado = $this->normalizarAntesDeValidar($inscrito);
        $mensagens = $this->mensagens($inscrito);

        if ($mensagens !== []) {
            throw ValidationException::withMessages([
                'inscritosSelecionados' => $mensagens,
            ]);
        }

        $complemento = $normalizado['complemento'];
        $payload = [
            'cep' => $normalizado['cep'],
            'logradouro' => $normalizado['logradouro'],
            'numero' => $normalizado['numero'],
            'bairro' => $normalizado['bairro'],
            'cidade' => $normalizado['cidade'],
            'uf' => $normalizado['uf'],
            'regiao' => '',
        ];

        if ($complemento !== '') {
            $payload = [
                'cep' => $payload['cep'],
                'logradouro' => $payload['logradouro'],
                'numero' => $payload['numero'],
                'complemento' => $complemento,
                'bairro' => $payload['bairro'],
                'cidade' => $payload['cidade'],
                'uf' => $payload['uf'],
                'regiao' => '',
            ];
        }

        return [
            'nome' => $normalizado['nome'],
            'endereco' => $payload,
        ];
    }

    /**
     * A API de pré-postagem exige CEP com 8 dígitos, sem hífen, e número em campo próprio.
     *
     * @return array{
     *     nome: string,
     *     cep: string,
     *     logradouro: string,
     *     numero: string,
     *     complemento: string,
     *     bairro: string,
     *     cidade: string,
     *     uf: string
     * }
     */
    private function normalizarAntesDeValidar(InterlabInscrito $inscrito): array
    {
        $endereco = $inscrito->laboratorio?->endereco;
        $partes = $this->separarLogradouroNumero(
            trim((string) $endereco?->endereco),
            trim((string) ($endereco?->complemento ?? '')),
        );

        return [
            'nome' => $this->nomeDoLaboratorio($inscrito),
            'cep' => preg_replace('/\D/', '', (string) ($endereco?->getRawOriginal('cep') ?? '')) ?? '',
            'logradouro' => $partes['logradouro'],
            'numero' => $partes['numero'],
            'complemento' => $partes['complemento'],
            'bairro' => trim((string) ($endereco?->bairro ?? '')),
            'cidade' => trim((string) ($endereco?->cidade ?? '')),
            'uf' => strtoupper(trim((string) ($endereco?->uf ?? ''))),
        ];
    }

    /**
     * @return list<string>
     */
    private function mensagensDeLogradouro(string $logradouro, string $numero, string $rotulo): array
    {
        $mensagens = [];

        if ($logradouro === '') {
            $mensagens[] = "Informe o logradouro do laboratório {$rotulo}.";
        } elseif (Str::length($logradouro) > 50) {
            $mensagens[] = "O logradouro do laboratório {$rotulo} deve ter no máximo 50 caracteres.";
        }

        if ($numero === '') {
            $mensagens[] = "Informe o número do endereço do laboratório {$rotulo}.";
        } elseif (Str::length($numero) > 6) {
            $mensagens[] = "O número do endereço do laboratório {$rotulo} deve ter até 6 caracteres.";
        }

        return $mensagens;
    }

    /**
     * Nome do laboratório cadastrado. Não usar o nome da empresa (`pessoas.nome_razao`).
     */
    private function nomeDoLaboratorio(InterlabInscrito $inscrito): string
    {
        return trim((string) ($inscrito->laboratorio?->nome ?? ''));
    }

    /**
     * O cadastro guarda o número no endereço ("Rua X, 117"), no fim do logradouro ou só no complemento ("117", "n. 73").
     *
     * @return array{logradouro: string, numero: string, complemento: string}
     */
    private function separarLogradouroNumero(string $endereco, string $complemento): array
    {
        $complemento = $this->complementoUtil($complemento);
        $separado = $this->numeroNoLogradouro($endereco);

        if ($separado['numero'] === '') {
            $extraido = $this->numeroNoComplemento($complemento);

            return [
                'logradouro' => $endereco,
                'numero' => $extraido['numero'],
                'complemento' => $extraido['complemento'],
            ];
        }

        return [
            'logradouro' => $separado['logradouro'],
            'numero' => $separado['numero'],
            'complemento' => $this->complementoDistintoDoNumero($complemento, $separado['numero'], $separado['resto']),
        ];
    }

    /**
     * @return array{logradouro: string, numero: string, resto: string}
     */
    private function numeroNoLogradouro(string $endereco): array
    {
        $vazio = [
            'logradouro' => $endereco,
            'numero' => '',
            'resto' => '',
        ];

        if ($endereco === '') {
            return $vazio;
        }

        $segmentos = preg_split('/\s*,\s*/u', $endereco) ?: [$endereco];

        if (count($segmentos) > 1) {
            for ($indice = count($segmentos) - 1; $indice >= 1; $indice--) {
                $numero = $this->numeroDeTrecho($segmentos[$indice]);

                if ($numero === null) {
                    continue;
                }

                return [
                    'logradouro' => trim(implode(', ', array_slice($segmentos, 0, $indice))),
                    'numero' => $numero,
                    'resto' => trim(implode(', ', array_slice($segmentos, $indice + 1))),
                ];
            }
        }

        return $this->numeroNoFimDoLogradouro($endereco) ?? $vazio;
    }

    /**
     * @return array{logradouro: string, numero: string, resto: string}|null
     */
    private function numeroNoFimDoLogradouro(string $endereco): ?array
    {
        if (preg_match('/^(.*?)[\s-]+(s\s*\/\s*n(?:\.|º|°)?)\s*$/iu', $endereco, $matches) === 1) {
            $numero = $this->numeroDeTrecho($matches[2]);
            $logradouro = trim($matches[1]);

            if ($numero !== null && $logradouro !== '') {
                return [
                    'logradouro' => $logradouro,
                    'numero' => $numero,
                    'resto' => '',
                ];
            }
        }

        if (preg_match('/^(.*\s+\S+)\s+(\d{1,6}[A-Za-z]?)$/u', $endereco, $matches) !== 1) {
            return null;
        }

        if (preg_match('/\bkm$/iu', trim($matches[1])) === 1) {
            return null;
        }

        return [
            'logradouro' => trim($matches[1]),
            'numero' => $matches[2],
            'resto' => '',
        ];
    }

    /**
     * @return array{numero: string, complemento: string}
     */
    private function numeroNoComplemento(string $complemento): array
    {
        if (preg_match('/^(?:n(?:º|°|\.)?\s*)?(\d{1,6}[A-Za-z]?)(?:\s*,\s*(.+))?$/iu', $complemento, $matches) !== 1) {
            return [
                'numero' => '',
                'complemento' => $complemento,
            ];
        }

        return [
            'numero' => $matches[1],
            'complemento' => trim($matches[2] ?? ''),
        ];
    }

    private function numeroDeTrecho(string $trecho): ?string
    {
        $trecho = trim($trecho);

        if (preg_match('/^s\s*\/?\s*n(?:\.|º|°)?$/iu', $trecho) === 1) {
            return 'S/N';
        }

        if (preg_match('/^(?:n(?:º|°|\.)?\s*)?(\d{1,6}[A-Za-z]?)$/iu', $trecho, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    private function complementoDistintoDoNumero(string $complemento, string $numero, string $resto): string
    {
        if ($this->numeroDeTrecho($complemento) === $numero) {
            $complemento = '';
        }

        $partes = array_values(array_filter(
            [$resto, $complemento],
            fn (string $parte): bool => $parte !== '',
        ));

        return implode(', ', $partes);
    }

    private function complementoUtil(string $complemento): string
    {
        if ($complemento === '' || preg_match('/^[-–—.]+$/u', $complemento) === 1) {
            return '';
        }

        return $complemento;
    }
}
