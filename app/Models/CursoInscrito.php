<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;


class CursoInscrito extends Model
{
    use LogsActivity, SetDefaultUid;

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
            ->useLogName(get_class($this));

        if (session('impersonator_id')) {
            $impersonator = User::find(session('impersonator_id'));
            $options->setDescriptionForEvent(function (string $eventName) use ($impersonator) {
                return "{$eventName} impersonated by {$impersonator->name}";
            });
        }
    }


    /**
     * Carrega curso
     * @return BelongsTo
     */
    public function agendaCurso(): BelongsTo
    {
        return $this->belongsTo(AgendaCursos::class);
    }

    /**
     * Carrega pessoa
     * @return BelongsTo
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class)->withTrashed();
    }

    /**
     * Carrega Empresa associado
     * @return HasOne
     */
    public function empresa(): HasOne
    {
        return $this->hasOne(Pessoa::class, 'id', 'empresa_id');
    }
}
