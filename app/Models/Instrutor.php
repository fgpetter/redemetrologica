<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instrutor extends Model
{
    use
        HasFactory,
        SoftDeletes;

    protected $table = 'instrutores';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Carrega pessoa
     * @return BelongsTo
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Cursos habilitados
     * @return HasMany
     */
    public function cursosHabilitados(): HasMany
    {
        return $this->hasMany(InstrutorCursoHabilitado::class);
    }
}
