<?php

namespace App\Http\Resources\Api\V1;

use App\Models\InterlabInscrito;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PepAgendaDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'agenda' => [
                'id' => $this->id,
                'uid' => $this->uid,
                'interlab_id' => $this->interlab_id,
                'status' => strtolower((string) $this->status),
                'inscricao' => (bool) $this->inscricao,
                'site' => (bool) $this->site,
                'destaque' => (bool) $this->destaque,
                'descricao' => $this->descricao,
                'data_inicio' => $this->data_inicio?->toDateString(),
                'data_fim' => $this->data_fim?->toDateString(),
                'instrucoes_inscricao' => $this->instrucoes_inscricao,
                'ano_referencia' => $this->ano_referencia,
                'data_limite_inscricao' => $this->data_limite_inscricao?->toDateString(),
                'valor_desconto' => $this->valor_desconto,
                'protocolo' => $this->protocolo,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'nome_interlab' => $this->interlab?->nome,
            'valores' => $this->valores->toArray(),
            'inscritos_por_empresa' => $this->inscritosPorEmpresa(),
        ];
    }

    /**
     * Agrupa inscritos por empresa e ordena, seguindo o mesmo padrão usado em
     * App\Livewire\Interlab\ListParticipantes: empresas pela inscrição mais
     * recente, inscritos dentro de cada empresa por data_inscricao desc.
     *
     * @return array<int, array<string, mixed>>
     */
    private function inscritosPorEmpresa(): array
    {
        $porEmpresa = $this->inscritos->groupBy('empresa_id');

        $empresaIds = $porEmpresa
            ->map(fn ($inscritos) => [
                'empresa_id' => $inscritos->first()->empresa_id,
                'data_mais_recente' => $inscritos->first()->data_inscricao,
            ])
            ->sortByDesc('data_mais_recente')
            ->pluck('empresa_id');

        $incluirAnalistas = $this->exportaInscritosPorAnalista();

        return $empresaIds->map(function ($empresaId) use ($porEmpresa, $incluirAnalistas) {
            $inscritos = $porEmpresa->get($empresaId)->sortByDesc('data_inscricao')->values();
            $empresa = $inscritos->first()->empresa;

            return [
                'empresa' => [
                    'id' => $empresa?->id,
                    'uid' => $empresa?->uid,
                    'nome_razao' => $empresa?->nome_razao,
                    'cpf_cnpj' => $empresa?->cpf_cnpj,
                    'associado' => (bool) $empresa?->associado,
                ],
                'inscritos' => $inscritos->map(fn ($inscrito) => $this->formataInscrito($inscrito, $incluirAnalistas))->all(),
            ];
        })->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function formataInscrito(InterlabInscrito $inscrito, bool $incluirAnalistas): array
    {
        $dados = [
            'id' => $inscrito->id,
            'uid' => $inscrito->uid,
            'pessoa_id' => $inscrito->pessoa_id,
            'empresa_id' => $inscrito->empresa_id,
            'laboratorio_id' => $inscrito->laboratorio_id,
            'pessoa_inscrito_id' => $inscrito->pessoa_inscrito_id,
            'agenda_interlab_id' => $inscrito->agenda_interlab_id,
            'data_inscricao' => $inscrito->data_inscricao,
            'valor' => $inscrito->valor,
            'pesquisa_id' => $inscrito->pesquisa_id,
            'resposta_pesquisa' => $inscrito->resposta_pesquisa,
            'certificado_emitido' => $inscrito->certificado_emitido,
            'certificado_path' => $inscrito->certificado_path,
            'informacoes_inscricao' => $inscrito->informacoes_inscricao,
            'responsavel_tecnico' => $inscrito->responsavel_tecnico,
            'telefone' => $inscrito->telefone,
            'email' => $inscrito->email,
            'lancamento_financeiro_id' => $inscrito->lancamento_financeiro_id,
            'senha_enviada' => $inscrito->senha_enviada,
            'created_at' => $inscrito->created_at,
            'updated_at' => $inscrito->updated_at,
            'laboratorio' => $inscrito->laboratorio,
        ];

        if ($incluirAnalistas) {
            $dados['analistas'] = $inscrito->analistas->map(fn ($analista) => [
                'id' => $analista->id,
                'uid' => $analista->uid,
                'nome' => $analista->nome,
                'email' => $analista->email,
                'telefone' => $analista->telefone,
                'created_at' => $analista->created_at,
                'updated_at' => $analista->updated_at,
            ])->all();
        }

        return $dados;
    }
}
