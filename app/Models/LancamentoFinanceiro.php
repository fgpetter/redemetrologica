<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LancamentoFinanceiro extends Model
{
    use HasFactory, LogsActivity, SetDefaultUid;

    protected $table = 'lancamentos_financeiros';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    /**
     * Carrega pesoa
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class)->withTrashed();
    }

    /**
     * Carrega centro de custo
     */
    public function centroCusto(): BelongsTo
    {
        return $this->belongsTo(CentroCusto::class)->withTrashed();
    }

    /**
     * Carrega curso
     */
    public function curso(): HasOne
    {
        return $this->hasOne(AgendaCursos::class, 'id', 'agenda_curso_id');
    }

    /**
     * Carrega interlab
     *
     * @return BelongsTo
     */
    public function interlab(): HasOne
    {
        return $this->hasOne(AgendaInterlab::class, 'id', 'agenda_interlab_id');
    }

    /**
     * Carrega avaliação
     *
     * @return BelongsTo
     */
    public function avaliacao(): HasOne
    {
        return $this->hasOne(AgendaInterlab::class, 'id', 'agenda_avaliacao_id');
    }

    /**
     * Retorna lancamentos a receber
     *
     * @param  array  $validated
     */
    public static function getLancamentosAReceber($validated): Builder
    {
        return self::select()
            ->with(['pessoa' => fn ($query) => $query->withTrashed()])
            ->with(['curso', 'interlab', 'avaliacao'])
            ->where('status', 'PROVISIONADO')
            ->whereNull('data_pagamento')
            ->where('tipo_lancamento', 'CREDITO')
            ->when($validated['data_inicial'] ?? null, function (Builder $query, $data_inicial) {
                $query->where('data_vencimento', '>=', $data_inicial);
            })
            ->when($validated['data_final'] ?? null, function (Builder $query, $data_final) {
                $query->where('data_vencimento', '<=', $data_final);
            })
            ->when($validated['pessoa'] ?? null, function (Builder $query, $pessoa) {
                $query->where('pessoa_id', $pessoa);
            })
            ->when($validated['area'] ?? null, function (Builder $query, $area) {
                $query->whereNotNull($area);
            })
            ->when($validated['curso'] ?? null, function (Builder $query, $curso) {
                $query->where('agenda_curso_id', $curso);
            })
            ->when($validated['pep'] ?? null, function (Builder $query, $pep) {
                $query->where('agenda_interlab_id', $pep);
            });
    }

    /**
     * Retorna lancamentos financeiros consolidados
     *
     * @param  array  $validated
     */
    public static function getLancamentosFinanceiros($validated): Builder
    {
        $tipoData = $validated['tipo_data'] ?? 'data_vencimento';

        return self::select()
            ->with(['pessoa' => function ($query) {
                $query->withTrashed();
            }])
            ->when($validated['data_inicial'] ?? null, function (Builder $query, $data_inicial) use ($tipoData) {
                $query->where($tipoData, '>=', $data_inicial);
            })
            ->when($validated['data_final'] ?? null, function (Builder $query, $data_final) use ($tipoData) {
                $query->where($tipoData, '<=', $data_final);
            })
            ->when($validated['pessoa'] ?? null, function (Builder $query, $pessoa) {
                $query->where('pessoa_id', $pessoa);
            });
    }
}
