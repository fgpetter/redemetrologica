<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Support\LogOptions;

class CursoDespesa extends Model
{
    use LogsModelActivity, SetDefaultUid;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(get_class($this));
    }

    protected $table = 'curso_despesas';

    /**
     * Carrega planos de contas atrelados
     */
    public function materialPadrao(): HasOne
    {
        return $this->hasOne(MaterialPadrao::class, 'id', 'material_padrao_id');
    }

    /**
     * Carrega agenda de cursos atrelados
     */
    public function agendaCurso(): BelongsTo
    {
        return $this->belongsTo(AgendaCursos::class);
    }
}
