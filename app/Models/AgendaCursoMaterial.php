<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgendaCursoMaterial extends Model
{
    use HasFactory;

    protected $table = 'agenda_curso_materiais';

    public function agendaCurso(): BelongsTo
    {
        return $this->belongsTo(AgendaCursos::class);
    }

    public function materiais(): HasMany
    {
        return $this->hasMany(CursoMaterial::class);
    }
}
