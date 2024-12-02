<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class InterlabRodada extends Model
{
    use LogsActivity;

    protected $table = 'interlab_rodadas';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['*'])
        ->useLogName( get_class($this) );
    }


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
