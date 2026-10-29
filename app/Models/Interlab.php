<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;

class Interlab extends Model
{
    use LogsModelActivity, SetDefaultUid, SoftDeletes;

    protected $table = 'interlabs';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(get_class($this));
    }

    /**
     * Get the agendas for the interlab.
     */
    public function agendas(): HasMany
    {
        return $this->hasMany(AgendaInterlab::class);
    }
}
