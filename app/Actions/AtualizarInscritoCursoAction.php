<?php

namespace App\Actions;

use App\Actions\Financeiro\AtualizarLancamentoCursoAction;
use App\Models\CursoInscrito;
use App\Models\Endereco;
use Illuminate\Support\Facades\DB;

class AtualizarInscritoCursoAction
{
    /**
     * Atualiza dados de um inscrito em curso, incluindo Pessoa, Endereco e financeiro.
     *
     * @param  array  $dados  Dados validados pelo UpdateInscricaoCursoRequest
     */
    public function execute(CursoInscrito $inscrito, array $dados): void
    {
        DB::transaction(function () use ($inscrito, $dados) {
            // 1. Montar updateData com valor, certificado_emitido, resposta_pesquisa
            $updateData = [
                'valor' => $dados['valor'] ?? null,
                'certificado_emitido' => $dados['certificado_emitido'] ?? null,
                'resposta_pesquisa' => $dados['resposta_pesquisa'] ?? null,
            ];

            // 2. Condicionalmente adicionar nome, email, telefone se presentes
            if (isset($dados['nome'])) {
                $updateData['nome'] = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($dados['nome']));
            }
            if (isset($dados['email'])) {
                $updateData['email'] = strtolower($dados['email']);
            }
            if (isset($dados['telefone'])) {
                $updateData['telefone'] = preg_replace('/[^0-9]/', '', $dados['telefone']) ?: null;
            }

            // 3. Atualizar inscrito
            $inscrito->update($updateData);

            // 4. Se for inscrição individual, atualiza os dados da Pessoa e Endereço
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

            // 5. Chamar AtualizarLancamentoCursoAction
            app(AtualizarLancamentoCursoAction::class)->execute($inscrito);
        });
    }
}
