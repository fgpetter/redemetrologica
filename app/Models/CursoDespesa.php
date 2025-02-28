<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;

class CursoDespesa extends Model
{
    use LogsActivity, SetDefaultUid;
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }


    protected $table = 'curso_despesas';

    /**
     * Carrega planos de contas atrelados
     * @return HasOne 
     */
    public function materialPadrao(): HasOne
    {
        return $this->hasOne(MaterialPadrao::class, 'id', 'material_padrao_id');
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
