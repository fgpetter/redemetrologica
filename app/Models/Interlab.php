<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;

class Interlab extends Model
{
    use SoftDeletes, LogsActivity, SetDefaultUid;

    protected $table = 'interlabs';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }
    /**
     * Get the agendas for the interlab.
     */
    public function agendas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AgendaInterlab::class);
    }
}
