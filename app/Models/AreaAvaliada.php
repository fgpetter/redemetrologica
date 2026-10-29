<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Support\LogOptions;

class AreaAvaliada extends Model
{
    use LogsModelActivity, SetDefaultUid;

    protected $table = 'areas_avaliadas';

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
     * Carrega avaliacao
     */
    public function avaliacao(): BelongsTo
    {
        return $this->belongsTo(AgendaAvaliacao::class, 'id', 'avaliacao_id');
    }

    /**
     * Carrega avaliador
     */
    public function avaliador(): HasOne
    {
        return $this->hasOne(Avaliador::class, 'id', 'avaliador_id');
    }

    /**
     * Carrega area atuacao
     */
    public function areaAtuacao(): HasOne
    {
        return $this->hasOne(AreaAtuacao::class, 'id', 'area_atuacao_id');
    }
}
