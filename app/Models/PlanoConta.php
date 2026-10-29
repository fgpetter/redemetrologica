<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;

class PlanoConta extends Model
{
    use LogsModelActivity, SetDefaultUid;

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
            ->logOnlyDirty()
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
