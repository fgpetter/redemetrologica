<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;



class AvaliacaoAvaliador extends Model
{
    use LogsActivity, SetDefaultUid;

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
            ->useLogName(get_class($this));
    }
}
