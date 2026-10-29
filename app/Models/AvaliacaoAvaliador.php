<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;

class AvaliacaoAvaliador extends Model
{
    use LogsModelActivity, SetDefaultUid;

    protected $table = 'avaliacao_avaliadores';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function agendaAvaliacao()
    {
        return $this->belongsTo(AgendaAvaliacao::class, 'agenda_avaliacao_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(get_class($this));
    }
}
