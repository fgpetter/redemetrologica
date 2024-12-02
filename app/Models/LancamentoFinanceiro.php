<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LancamentoFinanceiro extends Model
{
    use LogsActivity;
    
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
        ->useLogName( get_class($this) );
    }


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
