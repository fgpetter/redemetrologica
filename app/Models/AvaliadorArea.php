<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;

class AvaliadorArea extends Model
{
    use LogsModelActivity, SetDefaultUid;

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
            ->logOnlyDirty()
            ->useLogName(get_class($this));
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
