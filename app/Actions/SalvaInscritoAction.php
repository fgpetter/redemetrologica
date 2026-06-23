<?php

namespace App\Actions;

use App\Actions\Financeiro\AtualizarLancamentoCursoAction;
use App\Actions\Financeiro\GerarLancamentoCursoAction;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Models\Endereco;
use App\Models\Pessoa;
use Illuminate\Support\Facades\DB;

class SalvaInscritoAction
{
    /**
     * Cria inscrição em curso aberto (não IN-COMPANY), com Pessoa, Endereco,
     * CursoInscrito, LancamentoFinanceiro e e-mail de confirmação opcional.
     *
     * @param  array<string, mixed>  $dados
     */
    public function criar(AgendaCursos $agenda, array $dados, bool $enviarEmail = true): CursoInscrito
    {
        return DB::transaction(function () use ($agenda, $dados, $enviarEmail) {
            $nome = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($dados['nome']));
            $email = strtolower(trim($dados['email']));
            $telefone = preg_replace('/[^0-9]/', '', $dados['telefone'] ?? '');

            $tipoInscricao = strtolower((string) ($dados['tipo_inscricao'] ?? ''));
            $empresa = null;

            if ($tipoInscricao === 'cpf' && ($dados['idempotente'] ?? false) && isset($dados['pessoa_id'])) {
                $pessoa = Pessoa::query()->findOrFail($dados['pessoa_id']);
                $pessoa->update([
                    'nome_razao' => $nome,
                    'email' => $email,
                    'telefone' => $telefone,
                    'cpf_cnpj' => preg_replace('/[^0-9]/', '', (string) ($dados['cpf'] ?? $pessoa->cpf_cnpj)),
                ]);

                Endereco::updateOrCreate([
                    'pessoa_id' => $pessoa->id,
                ], [
                    'cep' => $dados['cep'] ?? null,
                    'uf' => $dados['uf'] ?? null,
                    'cidade' => $dados['cidade'] ?? null,
                    'bairro' => $dados['bairro'] ?? null,
                    'endereco' => $dados['endereco'] ?? null,
                    'complemento' => $dados['complemento'] ?? null,
                ]);

                $valor = $this->resolveValor($agenda, $pessoa, $dados['valor'] ?? null);

                $cursoInscrito = CursoInscrito::updateOrCreate(
                    [
                        'pessoa_id' => $pessoa->id,
                        'agenda_curso_id' => $agenda->id,
                        'empresa_id' => null,
                    ],
                    [
                        'valor' => $valor,
                        'data_inscricao' => now(),
                        'nome' => $nome,
                        'email' => $email,
                        'telefone' => $telefone ?: null,
                    ]
                );

                $lancamento = app(GerarLancamentoCursoAction::class)->execute(
                    $agenda,
                    $pessoa,
                    null,
                    false,
                    $valor,
                    $nome
                );
            } elseif ($tipoInscricao === 'cpf') {
                $pessoa = Pessoa::updateOrCreate([
                    'cpf_cnpj' => preg_replace('/[^0-9]/', '', (string) $dados['cpf']),
                ], [
                    'nome_razao' => $nome,
                    'email' => $email,
                    'telefone' => $telefone,
                    'tipo_pessoa' => 'PF',
                ]);

                Endereco::updateOrCreate([
                    'pessoa_id' => $pessoa->id,
                ], [
                    'cep' => $dados['cep'],
                    'uf' => $dados['uf'],
                    'cidade' => $dados['cidade'],
                    'bairro' => $dados['bairro'],
                    'endereco' => $dados['endereco'],
                    'complemento' => $dados['complemento'] ?? null,
                ]);

                $valor = $this->resolveValor($agenda, $pessoa, $dados['valor'] ?? null);

                $cursoInscrito = CursoInscrito::create([
                    'pessoa_id' => $pessoa->id,
                    'agenda_curso_id' => $agenda->id,
                    'empresa_id' => null,
                    'nome' => $nome,
                    'email' => $email,
                    'telefone' => $telefone ?: null,
                    'valor' => $valor,
                    'data_inscricao' => now(),
                ]);

                $lancamento = app(GerarLancamentoCursoAction::class)->execute(
                    $agenda,
                    $pessoa,
                    null,
                    false,
                    $valor,
                    $nome
                );
            } elseif ($tipoInscricao === 'cnpj' && ! empty($dados['empresa_id'])) {
                $empresa = Pessoa::query()->findOrFail($dados['empresa_id']);
                $valor = $this->resolveValor($agenda, $empresa, $dados['valor'] ?? null);

                $cursoInscrito = CursoInscrito::create([
                    'pessoa_id' => auth()->user()->pessoa->id,
                    'agenda_curso_id' => $agenda->id,
                    'empresa_id' => $empresa->id,
                    'nome' => $nome,
                    'email' => $email,
                    'telefone' => $telefone ?: null,
                    'valor' => $valor,
                    'data_inscricao' => now(),
                ]);

                $lancamento = app(GerarLancamentoCursoAction::class)->execute(
                    $agenda,
                    $empresa,
                    $empresa,
                    false,
                    $valor,
                    $nome
                );
            } else {
                throw new \InvalidArgumentException('Tipo de inscrição inválido ou dados incompletos.');
            }

            $cursoInscrito->update([
                'lancamento_financeiro_id' => $lancamento->id,
            ]);

            if ($enviarEmail) {
                app(NotifyInscricaoCursoAction::class)->executeParaInscrito($cursoInscrito, $agenda, $empresa);
            }

            return $cursoInscrito;
        });
    }

    /**
     * Atualiza inscrição em curso aberto (não IN-COMPANY), incluindo Pessoa, Endereco e financeiro.
     *
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(CursoInscrito $inscrito, array $dados): void
    {
        DB::transaction(function () use ($inscrito, $dados) {
            $updateData = [
                'valor' => $dados['valor'] ?? null,
                'certificado_emitido' => $dados['certificado_emitido'] ?? null,
                'resposta_pesquisa' => $dados['resposta_pesquisa'] ?? null,
            ];

            if (isset($dados['nome'])) {
                $updateData['nome'] = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($dados['nome']));
            }
            if (isset($dados['email'])) {
                $updateData['email'] = strtolower($dados['email']);
            }
            if (isset($dados['telefone'])) {
                $updateData['telefone'] = preg_replace('/[^0-9]/', '', $dados['telefone']) ?: null;
            }

            $inscrito->update($updateData);

            if (! $inscrito->empresa_id) {
                $pessoa = $inscrito->pessoa;
                $pessoa->update([
                    'nome_razao' => $updateData['nome'] ?? $pessoa->nome_razao,
                    'email' => $updateData['email'] ?? $pessoa->email,
                    'telefone' => $updateData['telefone'] ?? $pessoa->telefone,
                    'cpf_cnpj' => preg_replace('/[^0-9]/', '', $dados['cpf'] ?? ''),
                ]);

                Endereco::updateOrCreate([
                    'pessoa_id' => $pessoa->id,
                ], [
                    'cep' => $dados['cep'] ?? null,
                    'uf' => $dados['uf'] ?? null,
                    'cidade' => $dados['cidade'] ?? null,
                    'bairro' => $dados['bairro'] ?? null,
                    'endereco' => $dados['endereco'] ?? null,
                    'complemento' => $dados['complemento'] ?? null,
                ]);
            }

            app(AtualizarLancamentoCursoAction::class)->execute($inscrito);
        });
    }

    /**
     * @param  mixed  $valorManual
     */
    private function resolveValor(AgendaCursos $agenda, Pessoa $referencia, $valorManual): mixed
    {
        if ($valorManual !== null && $valorManual !== '') {
            return $valorManual;
        }

        return (int) $referencia->associado === 1
            ? $agenda->investimento_associado
            : $agenda->investimento;
    }
}
