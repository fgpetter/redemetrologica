<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;

class Convite extends Model
{
    protected $table = 'convites';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }


    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function agendaCurso()
    {
        return $this->belongsTo(AgendaCursos::class, 'agenda_curso_id');
    }

    public function agendaInterlab()
    {
        return $this->belongsTo(AgendaInterlab::class, 'agenda_interlab_id');
    }

    public function empresaUid(): string
    {
        return $this->pessoa->empresas()->first()->uid;
    }
}
