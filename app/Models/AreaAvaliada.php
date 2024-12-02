<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class AreaAvaliada extends Model
{
    use LogsActivity;

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
        ->useLogName( get_class($this) );
    }


    /**
     * Carrega avaliacao
     * @return BelongsTo
     */
    public function avaliacao() : BelongsTo
    {
        return $this->belongsTo(AgendaAvaliacao::class, 'id', 'avaliacao_id');
    }

    /**
     * Carrega avaliador
     * @return HasOne
     */
    public function avaliador() : HasOne
    {
        return $this->hasOne(Avaliador::class, 'id', 'avaliador_id');
    }

    /**
     * Carrega area atuacao
     * @return HasOne
     */
    public function areaAtuacao() : HasOne
    {
        return $this->hasOne(AreaAtuacao::class, 'id', 'area_atuacao_id');
    }




}
