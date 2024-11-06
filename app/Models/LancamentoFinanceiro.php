<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LancamentoFinanceiro extends Model
{
    use HasFactory;
    
    protected $table = 'lancamentos_financeiros';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Carrega pesoa
     * @return BelongsTo
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Carrega centro de custo
     * @return hasOne
     */
    public function centroCusto(): HasOne
    {
        return $this->hasOne(CentroCusto::class);
    }

    public function curso(): HasOne
    {
        return $this->hasOne(AgendaCursos::class, 'id', 'agenda_curso_id');
    }
    
}
