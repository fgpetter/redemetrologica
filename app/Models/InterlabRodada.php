<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InterlabRodada extends Model
{
    use LogsActivity, SetDefaultUid;

    protected $table = 'interlab_rodadas';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    /**
     * Carrega Agenda Interlab associada
     */
    public function agendainterlab(): BelongsTo
    {
        return $this->belongsTo(AgendaInterlab::class);
    }
}
