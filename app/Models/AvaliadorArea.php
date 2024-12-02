<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class AvaliadorArea extends Model
{
    use LogsActivity;

    protected $table = 'avaliador_areas';

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


    public function avaliador()
    {
        return $this->belongsTo(Avaliador::class);
    }

    public function area()
    {
        return $this->belongsTo(AreaAtuacao::class, 'area_id', 'id');
    }

}
