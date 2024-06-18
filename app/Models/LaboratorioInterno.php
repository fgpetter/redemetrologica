<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaboratorioInterno extends Model
{
    use HasFactory;

    protected $table = 'laboratorios_internos';

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
    public function laboratorio() : BelongsTo
    {
        return $this->belongsTo(Laboratorio::class);
    }

    /**
     * Carrega Empresa associado
     * @return HasOne
     */
    public function area(): HasOne
    {
        return $this->hasOne(AreaAtuacao::class, 'id', 'area_atuacao_id');
    }

}
