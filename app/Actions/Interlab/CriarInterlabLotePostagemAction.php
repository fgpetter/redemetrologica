<?php

namespace App\Actions\Interlab;

use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Integrations\Correios\CorreiosLog;
use App\Jobs\Interlab\ClassificarServicoLotePostagemJob;
use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;
use App\Models\InterlabLotePostagem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CriarInterlabLotePostagemAction
{
    public function __construct(private DestinatarioInterlabSnapshot $destinatario) {}

    /**
     * Persiste o lote e um item por laboratório, com snapshot do destinatário e nu_requisicao imutável.
     *
     * @param  list<int>  $inscritoIds
     * @param  array{
     *     nome: string,
     *     codigo_formato: string,
     *     altura: ?string,
     *     largura: ?string,
     *     comprimento: ?string,
     *     diametro: ?string,
     *     peso_gramas: int,
     *     declaracao_conteudo: list<array{conteudo: string, quantidade: string, valor: string}>
     * }  $parametros
     */
    public function execute(AgendaInterlab $agenda, array $inscritoIds, array $parametros): InterlabLotePostagem
    {
        $ids = array_values(array_unique(array_map(intval(...), $inscritoIds)));

        if ($ids === []) {
            throw ValidationException::withMessages([
                'inscritosSelecionados' => 'Selecione ao menos um laboratório.',
            ]);
        }

        $this->garantirInscritosDaAgenda($agenda, $ids);

        $lote = DB::transaction(function () use ($agenda, $ids, $parametros): InterlabLotePostagem {
            AgendaInterlab::query()->whereKey($agenda->id)->lockForUpdate()->first();

            if ($agenda->lotesPostagem()->emProcessamento()->exists()) {
                throw ValidationException::withMessages([
                    'inscritosSelecionados' => 'Já existe um lote de postagem em processamento nesta agenda.',
                ]);
            }

            $inscritos = $this->inscritosDaSelecao($agenda, $ids);
            $this->garantirLaboratoriosUnicos($inscritos);
            $this->garantirDestinatarios($inscritos);

            $lote = InterlabLotePostagem::query()->create([
                'agenda_interlab_id' => $agenda->id,
                'nome' => $parametros['nome'],
                'codigo_formato' => $parametros['codigo_formato'],
                'altura' => $parametros['altura'],
                'largura' => $parametros['largura'],
                'comprimento' => $parametros['comprimento'],
                'diametro' => $parametros['diametro'],
                'peso_gramas' => $parametros['peso_gramas'],
                'declaracao_conteudo' => $parametros['declaracao_conteudo'],
                'status' => InterlabLotePostagemStatus::ClassificandoServico,
                'ultima_progressao_em' => now(),
            ]);

            foreach ($inscritos as $inscrito) {
                $lote->itens()->create([
                    'interlab_inscrito_id' => $inscrito->id,
                    'interlab_laboratorio_id' => $inscrito->laboratorio_id,
                    'nu_requisicao' => (string) Str::uuid(),
                    'destinatario' => $this->destinatario->montar($inscrito),
                    'status' => InterlabLotePostagemItemStatus::AguardandoClassificacao,
                ]);
            }

            return $lote->load('itens');
        });

        ClassificarServicoLotePostagemJob::dispatch($lote)->afterCommit();

        CorreiosLog::info('lote.criado', [
            'lote_id' => $lote->id,
            'lote_uid' => $lote->uid,
            'nome' => $lote->nome,
            'agenda_id' => $lote->agenda_interlab_id,
            'itens' => $lote->itens->count(),
            'item_ids' => $lote->itens->pluck('id')->all(),
        ]);

        return $lote;
    }

    /**
     * @param  list<int>  $ids
     */
    private function garantirInscritosDaAgenda(AgendaInterlab $agenda, array $ids): void
    {
        $pertencentes = InterlabInscrito::query()
            ->where('agenda_interlab_id', $agenda->id)
            ->whereIn('id', $ids)
            ->whereNotNull('laboratorio_id')
            ->count();

        if ($pertencentes !== count($ids)) {
            throw ValidationException::withMessages([
                'inscritosSelecionados' => 'A seleção contém inscrições que não pertencem a esta agenda.',
            ]);
        }
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, InterlabInscrito>
     */
    private function inscritosDaSelecao(AgendaInterlab $agenda, array $ids): Collection
    {
        return InterlabInscrito::query()
            ->where('agenda_interlab_id', $agenda->id)
            ->whereIn('id', $ids)
            ->whereNotNull('laboratorio_id')
            ->with(['laboratorio.endereco'])
            ->get();
    }

    /**
     * @param  Collection<int, InterlabInscrito>  $inscritos
     */
    private function garantirLaboratoriosUnicos(Collection $inscritos): void
    {
        $laboratorios = $inscritos->pluck('laboratorio_id');

        if ($laboratorios->unique()->count() !== $laboratorios->count()) {
            throw ValidationException::withMessages([
                'inscritosSelecionados' => 'Um laboratório só pode ser selecionado uma vez no mesmo lote.',
            ]);
        }
    }

    /**
     * @param  Collection<int, InterlabInscrito>  $inscritos
     */
    private function garantirDestinatarios(Collection $inscritos): void
    {
        $mensagens = $inscritos
            ->flatMap(fn (InterlabInscrito $inscrito): array => $this->destinatario->mensagens($inscrito))
            ->all();

        if ($mensagens !== []) {
            throw ValidationException::withMessages([
                'inscritosSelecionados' => $mensagens,
            ]);
        }
    }
}
