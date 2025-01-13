<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class AgendaCursos extends Model
{
    use LogsActivity;

    protected $table = 'agenda_cursos';

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
     * Carrega curso
     * @return BelongsTo
     */
    public function curso() : BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * Carrega custos atrelados
     * @return HasMany 
     */
    public function despesas(): HasMany
    {
        return $this->hasMany(CursoDespesa::class);
    }

    public function instrutor() : BelongsTo
    {
        return $this->belongsTo(Instrutor::class)->withTrashed();
    }

    public function inscritos() : HasMany
    {
        return $this->hasMany(CursoInscrito::class, 'agenda_curso_id', 'id');
    }
}
