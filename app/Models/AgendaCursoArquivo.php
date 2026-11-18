<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;

class AgendaCursoArquivo extends Model
{
    use LogsModelActivity, SetDefaultUid;

    protected $table = 'agenda_curso_arquivos';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*'])->logOnlyDirty()->useLogName(get_class($this));
    }

    public function agendaCurso(): BelongsTo
    {
        return $this->belongsTo(AgendaCursos::class, 'agenda_curso_id');
    }
}
