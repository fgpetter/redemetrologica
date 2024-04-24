<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CursoDespesa extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'curso_despesas';

    /**
     * Carrega planos de contas atrelados
     * @return HasOne 
     */
    public function planoConta(): HasOne
    {
        return $this->hasOne(PlanoConta::class, 'id', 'plano_conta_id');
    }

    /**
     * Carrega agenda de cursos atrelados
     * @return BelongsTo
     */
    public function agendaCurso(): BelongsTo
    {
        return $this->belongsTo(AgendaCursos::class);
    }
}
