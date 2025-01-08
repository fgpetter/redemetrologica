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

    /**
     * Atualiza lista de parametros da rodada
     * Remove antigos e insere novos
     *
     * @param array $parametros
     * @return void
     */
    public function updateParametros($parametros): void
    {
        $this->parametros()->delete();
        if(!empty($parametros)) {
            foreach ($parametros as $parametro) {
                $this->parametros()->create(
                    [
                        'parametro_id' => $parametro,
                        'agenda_interlab_id' => $this->agenda_interlab_id,
                    ]);
            }
        }
    }
}
