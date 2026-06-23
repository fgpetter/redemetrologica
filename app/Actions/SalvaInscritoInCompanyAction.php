<?php

namespace App\Actions;

use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Models\LancamentoFinanceiro;
use Illuminate\Support\Facades\DB;

class SalvaInscritoInCompanyAction
{
    /**
     * Cria inscrição em curso IN-COMPANY sem alterar lançamento financeiro existente.
     */
    public function criar(AgendaCursos $agenda, string $nome, string $email, ?string $telefone): CursoInscrito
    {
        return DB::transaction(function () use ($agenda, $nome, $email, $telefone) {
            $nome = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($nome));
            $email = strtolower(trim($email));
            $telefone = preg_replace('/[^0-9]/', '', $telefone ?? '');

            $cursoInscrito = CursoInscrito::create([
                'pessoa_id' => auth()->user()->pessoa->id,
                'agenda_curso_id' => $agenda->id,
                'empresa_id' => $agenda->empresa_id,
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone ?: null,
                'valor' => null,
                'data_inscricao' => now(),
            ]);

            if ($agenda->empresa_id) {
                $lancamento = LancamentoFinanceiro::query()
                    ->where('pessoa_id', $agenda->empresa_id)
                    ->where('agenda_curso_id', $agenda->id)
                    ->first();

                if ($lancamento) {
                    $cursoInscrito->update([
                        'lancamento_financeiro_id' => $lancamento->id,
                    ]);
                }
            }

            return $cursoInscrito;
        });
    }

    /**
     * Atualiza inscrição em curso IN-COMPANY sem tocar em lançamento, Pessoa ou Endereco.
     *
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(CursoInscrito $inscrito, array $dados): void
    {
        DB::transaction(function () use ($inscrito, $dados) {
            $updateData = [
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
        });
    }
}
