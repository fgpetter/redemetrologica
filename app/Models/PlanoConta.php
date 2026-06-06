<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PlanoConta extends Model
{
    use LogsActivity, SetDefaultUid;

    protected $table = 'plano_contas';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /** ID do plano RECEITA PRESTAÇÃO DE SERVIÇOS (lançamentos automáticos de inscrição). */
    public const ID_RECEITA_PRESTACAO_SERVICOS = 3;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('Usuários');
    }

    /**
     * Retorna o centro de custo atrelado
     */
    public function centrocusto(): BelongsTo
    {
        return $this->belongsTo(CentroCusto::class, 'centro_custo_id', 'id');
    }
}
