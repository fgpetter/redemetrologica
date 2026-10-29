<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;

class InstrutorCursoHabilitado extends Model
{
    use LogsModelActivity, SetDefaultUid;

    protected $table = 'instrutor_curso_habilitado';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(get_class($this));
    }

    /**
     * Carrega curso
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }
}
