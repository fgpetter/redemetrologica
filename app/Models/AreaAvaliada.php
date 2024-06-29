<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AreaAvaliada extends Model
{
    use HasFactory;

    protected $table = 'areas_avaliadas';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Carrega avaliacao
     * @return BelongsTo
     */
    public function avaliacao() : BelongsTo
    {
        return $this->belongsTo(AgendaAvaliacao::class, 'id', 'avaliacao_id');
    }

    /**
     * Carrega avaliador
     * @return HasOne
     */
    public function avaliador() : HasOne
    {
        return $this->hasOne(Avaliador::class, 'id', 'avaliador_id');
    }

    /**
     * Carrega area atuacao
     * @return HasOne
     */
    public function areaAtuacao() : HasOne
    {
        return $this->hasOne(AreaAtuacao::class, 'id', 'area_atuacao_id');
    }




}
