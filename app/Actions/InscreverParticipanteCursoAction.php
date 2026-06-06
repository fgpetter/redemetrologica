<?php

namespace App\Actions;

use App\Actions\Financeiro\GerarLancamentoCursoAction;
use App\Exceptions\InvalidEmailException;
use App\Mail\ConfirmacaoInscricaoCursoNotification;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Models\Endereco;
use App\Models\Pessoa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InscreverParticipanteCursoAction
{
    /**
     * Inscreve um participante em um curso, criando/atualizando Pessoa, Endereco,
     * CursoInscrito, LancamentoFinanceiro e enviando e-mail de confirmação.
     *
     * @param  array  $dados  Dados validados pelo StoreInscricaoCursoRequest
     */
    public function execute(array $dados): CursoInscrito
    {
        return DB::transaction(function () use ($dados) {
            // 1. Sanitizar inputs
            $nome = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($dados['nome']));
            $email = strtolower($dados['email']);
            $telefone = preg_replace('/[^0-9]/', '', $dados['telefone'] ?? '');

            $pessoa_id = auth()->user()->pessoa->id; // temporário
            $empresa_id = $dados['empresa_id'] ?? null;

            // 2. Se tipo_inscricao == 'cpf': updateOrCreate Pessoa + Endereco
            if ($dados['tipo_inscricao'] == 'cpf') {
                $pessoa = Pessoa::updateOrCreate([
                    'cpf_cnpj' => preg_replace('/[^0-9]/', '', $dados['cpf']),
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
                    'complemento' => $dados['complemento'],
                ]);

                $pessoa_id = $pessoa->id;
                $empresa_id = null;
            }

            // 3. Criar CursoInscrito
            $novoInscrito = CursoInscrito::create([
                'pessoa_id' => $pessoa_id,
                'agenda_curso_id' => $dados['agenda_curso_id'],
                'empresa_id' => $empresa_id,
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone ?: null,
                'valor' => $dados['valor'] ?? null,
                'data_inscricao' => now(),
            ]);

            // 4. Chamar GerarLancamentoCursoAction
            $agendacurso = AgendaCursos::find($dados['agenda_curso_id']);
            if ($dados['tipo_inscricao'] == 'cnpj' && $empresa_id) {
                $empresa = Pessoa::find($empresa_id);
                $lancamento = app(GerarLancamentoCursoAction::class)->execute($agendacurso, $empresa, $empresa, false, $dados['valor'] ?? null, $nome);
            } elseif ($dados['tipo_inscricao'] == 'cpf') {
                $pessoa = Pessoa::find($pessoa_id);
                $lancamento = app(GerarLancamentoCursoAction::class)->execute($agendacurso, $pessoa, null, false, $dados['valor'] ?? null, $nome);
            }

            // 5. Vincular lancamento_id ao CursoInscrito
            $novoInscrito->update([
                'lancamento_financeiro_id' => $lancamento->id,
            ]);

            // 6. Enviar e-mail de confirmação
            $dadosParticipante = [
                'nome' => $novoInscrito->nome,
                'email' => $novoInscrito->email,
                'telefone' => $novoInscrito->telefone ?? '',
                'empresa_nome' => (isset($empresa) && $empresa) ? $empresa->nome_razao : null,
            ];

            if (empty($novoInscrito->email)) {
                $content = [
                    'class' => self::class,
                    'inscrito_id' => $novoInscrito->id,
                    'inscrito_pessoa_uid' => $novoInscrito->pessoa?->id ?? '',
                ];
                new InvalidEmailException($content);
            } else {
                Mail::to($novoInscrito->email)
                    ->queue(new ConfirmacaoInscricaoCursoNotification($dadosParticipante, $agendacurso));
            }

            return $novoInscrito;
        });
    }
}
