<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;

class InterlabRodadaParametro extends Model
{
    use LogsActivity;

    protected $table = 'interlab_rodada_parametros';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }


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
