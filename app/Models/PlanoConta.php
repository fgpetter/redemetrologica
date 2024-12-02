<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class PlanoConta extends Model
{
    use LogsActivity;

    protected $table = 'plano_contas';

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
        ->useLogName('Usuários');
    }



    /**
     * Retorna o centro de custo atrelado
     *
     * @return BelongsTo
     */
    public function centrocusto(): BelongsTo
    {
        return $this->belongsTo(CentroCusto::class ,'centro_custo_id', 'id');
    }
}
