<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InterlabRodadaParametro extends Model
{
    use HasFactory;

    protected $table = 'interlab_rodada_parametros';

    protected $guarded = [];

    /**
     * Carrega rodada associada
     *
     * @return BelongsTo
     */
    public function rodada(): BelongsTo
    {
        return $this->belongsTo(InterlabRodada::class);
    }

    /**
     * Carrega agenda interlab
     *
     * @return BelongsTo
     */
    public function agendainterlab(): BelongsTo
    {
        return $this->belongsTo(AgendaInterlab::class);
    }

    /**
     * Carrega parametro
     *
     * @return BelongsTo
     */
    public function parametro(): BelongsTo
    {
        return $this->belongsTo(Parametro::class);
    }
}
