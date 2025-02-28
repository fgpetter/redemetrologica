<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;

class InterlabParametro extends Model
{
    use LogsActivity;

    protected $table = 'interlab_parametros';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }


    /**
     * Carrega Parametro associado
     * @return BelongsTo
     */
    public function parametro(): BelongsTo
    {
        return $this->belongsTo(Parametro::class);
    }
}
