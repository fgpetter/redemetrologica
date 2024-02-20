<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanoConta extends Model
{
    use HasFactory;

    protected $table = 'plano_contas';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


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
