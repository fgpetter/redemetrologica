<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;
use App\Models\User;



class InterlabInscrito extends Model
{

    use LogsActivity, SetDefaultUid;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'interlab_inscritos';

    public function getActivitylogOptions(): LogOptions
    {
        $options = LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));

        if (session('impersonator_id')) {
            $impersonator = User::find(session('impersonator_id'));
            $options->setDescriptionForEvent(function(string $eventName) use ($impersonator) {
                return "{$eventName} impersonated by {$impersonator->name}";
            });
        }

        return $options;
    }


    /**
     * Agenda interlab da inscrição
     * @return BelongsTo
     */
    public function agendaInterlab() : BelongsTo
    {
        return $this->belongsTo(AgendaInterlab::class);
    }

    /**
     * Pessoa que realizou a inscrição
     * @return BelongsTo
     */
    public function pessoa() : BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Empresa relacionada a inscrição, para cobrança
     * @return BelongsTo
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'empresa_id', 'id');
    }

    /**
     * Laboratório inscrito no PEP
     * @return BelongsTo
     */
    public function laboratorio() : HasOne
    {
        return $this->hasOne(InterlabLaboratorio::class, 'id', 'laboratorio_id');
    }

    /**
     * Pessoa inscrita no PEP
     * @return BelongsTo
     */
    public function pessoaInscrita(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'empresa_id', 'id');
    }


}
