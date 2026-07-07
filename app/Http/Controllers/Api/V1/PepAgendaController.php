<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListPepAgendasRequest;
use App\Http\Resources\Api\V1\PepAgendaDetailResource;
use App\Http\Resources\Api\V1\PepAgendaListResource;
use App\Models\AgendaInterlab;
use Illuminate\Http\JsonResponse;

class PepAgendaController extends Controller
{
    /**
     * Lista agendas PEP (agenda_interlabs) com filtros opcionais de status, ano e período.
     */
    public function index(ListPepAgendasRequest $request): JsonResponse
    {
        $status = $request->input('status');
        $ano = $request->input('ano');
        $datainicio = $request->input('datainicio');
        $datafim = $request->input('datafim');

        $agendas = AgendaInterlab::query()
            ->select('agenda_interlabs.*')
            ->join('interlabs', 'interlabs.id', '=', 'agenda_interlabs.interlab_id')
            ->with('interlab')
            ->when($status, fn ($query, $status) => $query->where('agenda_interlabs.status', strtoupper($status)))
            ->when($ano, fn ($query, $ano) => $query->where('agenda_interlabs.ano_referencia', $ano))
            ->when($datainicio && $datafim, fn ($query) => $query->whereBetween('agenda_interlabs.data_inicio', [$datainicio, $datafim]))
            ->when($datainicio && ! $datafim, fn ($query) => $query->where('agenda_interlabs.data_inicio', '>=', $datainicio))
            ->when(! $datainicio && $datafim, fn ($query) => $query->where('agenda_interlabs.data_inicio', '<=', $datafim))
            ->orderByDesc('agenda_interlabs.data_inicio')
            ->orderBy('interlabs.nome')
            ->get();

        return PepAgendaListResource::collection($agendas)
            ->additional(['meta' => ['total' => $agendas->count()]])
            ->response();
    }

    /**
     * Detalha uma agenda PEP, com inscritos agrupados por empresa.
     */
    public function show(string $uid): JsonResponse
    {
        $agenda = AgendaInterlab::query()
            ->with([
                'interlab',
                'valores',
                'inscritos.empresa',
                'inscritos.laboratorio',
                'inscritos.analistas',
            ])
            ->where('uid', $uid)
            ->first();

        if (! $agenda) {
            return response()->json(['message' => 'Agenda PEP não encontrada.'], 404);
        }

        return (new PepAgendaDetailResource($agenda))->response();
    }
}
