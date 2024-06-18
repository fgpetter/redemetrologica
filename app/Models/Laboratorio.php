<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Laboratorio extends Model
{
    use HasFactory;

    protected $table = 'laboratorios';

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
    public function pessoa() : BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }
    
    /**
     * Carrega endereço
     * @return BelongsTo
     */
    public function endereco() : BelongsTo
    {
        return $this->belongsTo(Endereco::class);
    }

        /**
     * Cursos habilitados
     * @return HasMany
     */
    public function laboratoriosInternos(): HasMany
    {
        return $this->hasMany(LaboratorioInterno::class);
    }

}
