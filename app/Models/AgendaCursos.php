<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgendaCursos extends Model
{
    use HasFactory;

    protected $table = 'agenda_cursos';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Carrega curso
     * @return BelongsTo
     */
    public function curso() : BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }
}
