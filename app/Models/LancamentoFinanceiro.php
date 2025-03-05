<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\SetDefaultUid;

class LancamentoFinanceiro extends Model
{
  use LogsActivity, SetDefaultUid;

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
   * @return BelongsTo
   */
  public function pessoa(): BelongsTo
  {
    return $this->belongsTo(Pessoa::class)->withTrashed();
  }

  /**
   * Carrega centro de custo
   * @return BelongsTo
   */
  public function centroCusto(): BelongsTo
  {
    return $this->belongsTo(CentroCusto::class)->withTrashed();
  }

  /**
   * Carrega curso
   * @return hasOne
   */
  public function curso(): HasOne
  {
    return $this->hasOne(AgendaCursos::class, 'id', 'agenda_curso_id');
  }

  /**
   * Carrega interlab
   * @return BelongsTo
   */
  public function interlab(): HasOne
  {
    return $this->hasOne(AgendaInterlab::class, 'id', 'agenda_interlab_id');
  }

  /**
   * Carrega avaliação
   * @return BelongsTo
   */
  public function avaliacao(): HasOne
  {
    return $this->hasOne(AgendaInterlab::class, 'id', 'agenda_avaliacao_id');
  }

  /**
   * Retorna lancamentos a receber
   *
   * @param array $validated
   * @return Builder
   */
  public static function getLancamentosAReceber($validated): Builder
  {
    return self::select()
      ->with(['pessoa' => fn($query) => $query->withTrashed()])
      ->with(['curso', 'interlab', 'avaliacao'])
      ->where('status', 'PROVISIONADO')
      ->where('tipo_lancamento', 'CREDITO')
      // ->where(function ($query) {
      //   $query->whereNull('agenda_curso_id') // seleciona todos lancamentos que nao possuem agenda
      //     ->orWhere(function ($query) {
      //       $query->whereNotNull('agenda_curso_id') // se possui agenda
      //         ->whereHas('curso', function ($query) {
      //           $query->whereIn('status', ['CONFIRMADO', 'REALIZADO']); // seleciona somente os confirmados ou realizados
      //         });
      //     });
      // })
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
   * @param array $validated
   * @return Builder
   */
  public static function getLancamentosFinanceiros($validated): Builder
  {
    return self::select()
      ->with(['pessoa' => function ($query) {
        $query->withTrashed();
      }])

      ->when($validated['data_inicial'] ?? null, function (Builder $query, $data_inicial) {
        $query->where('data_vencimento', '>=', $data_inicial);
      }, function (Builder $query) {
        $query->where('data_vencimento', '>=', today()); // default
      })

      ->when($validated['data_final'] ?? null, function (Builder $query, $data_final) {
        $query->where('data_vencimento', '<=', $data_final);
      }, function (Builder $query) {
        $query->where('data_vencimento', '<=', $validated['data_final'] ?? today()->addDays(7)); // default
      })

      ->when($validated['pessoa'] ?? null, function (Builder $query, $pessoa) {
        $query->where('pessoa_id', $pessoa);
      });
  }
}
