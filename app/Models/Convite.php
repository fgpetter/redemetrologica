<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Convite extends Model
{
    protected $table = 'convites';

    protected $guarded = [];

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
        return $this->belongsTo(Interlab::class);
    }

    public function empresaUid(): string
    {
        return $this->pessoa->empresas()->first()->uid;
    }
}
