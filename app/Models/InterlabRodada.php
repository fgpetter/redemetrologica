<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterlabRodada extends Model
{
    use HasFactory;

    protected $table = 'interlab_rodadas';

    protected $guarded = [];

    /**
     * Carrega Agenda Interlab associada
     * @return BelongsTo
     */
    public function agendainterlab(): BelongsTo
    {
        return $this->belongsTo(AgendaInterlab::class);
    }

    /**
     * Carrega Parametros associados
     * @return HasMany
     */

    public function parametros(): HasMany
    {
        return $this->hasMany(InterlabRodadaParametro::class);
    }
}
