<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;


class AgendaAvaliacao extends Model
{
    use LogsActivity, SetDefaultUid;

    protected $table = 'agenda_avaliacoes';

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
        ->useLogName( get_class($this) );
    }

    /**
     * Carrega areas avaliadas
     * @return HasMany 
     */
    public function areas(): HasMany
    {
        return $this->hasMany(AreaAvaliada::class, 'avaliacao_id', 'id');
    }

    public function laboratorio(): HasOne
    {
        return $this->hasOne(Laboratorio::class, 'id', 'laboratorio_id');
    }

    public function tipoAvaliacao(): HasOne
    {
        return $this->hasOne(TipoAvaliacao::class, 'id', 'tipo_avaliacao_id');
    }

}
