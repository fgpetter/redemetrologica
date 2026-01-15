<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaCursos extends Model
{
    use LogsActivity, SetDefaultUid;

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

    public function cursoMateriais()
    {
        return $this->belongsToMany(CursoMaterial::class, 'agenda_curso_materiais', 'agenda_curso_id', 'curso_material_id');
    }
}
