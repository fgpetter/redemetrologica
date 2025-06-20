<?php

namespace App\Models;

use App\Models\Interlab;
use App\Traits\SetDefaultUid;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class AgendaInterlab extends Model
{
    use LogsActivity, SetDefaultUid;

    protected $table = 'agenda_interlabs';

    protected $guarded = [];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['*'])
        ->useLogName( get_class($this) );
    }

    /**
     * Retorna interlab associado
     *
     * @return BelongsTo
     */
    public function interlab(): BelongsTo
    {
        return $this->belongsTo(Interlab::class);
    }

    /**
     * Retorna despesas associadas
     *
     * @return HasMany
     */
    public function despesas(): HasMany
    {
        return $this->hasMany(InterlabDespesa::class);
    }

    /**
     * Retorna parametros associados
     *
     * @return HasMany
     */
    public function parametros(): HasMany
    {
        return $this->hasMany(InterlabParametro::class);
    }

    /**
     * Retorna rodadas associados
     *
     * @return HasMany
     */
    public function rodadas(): HasMany
    {
        return $this->hasMany(InterlabRodada::class);
    }

    public function inscritos(): HasMany
    {
        return $this->hasMany(InterlabInscrito::class, 'agenda_interlab_id', 'id');
    }

    public function materiais(): HasMany
    {
        return $this->hasMany(AgendainterlabMaterial::class);
    }

}
