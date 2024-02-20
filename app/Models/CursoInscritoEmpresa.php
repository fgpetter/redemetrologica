<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CursoInscritoEmpresa extends Model
{
    use HasFactory;

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
    public function agendaCurso() : BelongsTo
    {
        return $this->belongsTo(AgendaCursos::class);
    }

    /**
     * Carrega pessoa
     * @return BelongsTo
     */
    public function pessoa() : BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }
}
