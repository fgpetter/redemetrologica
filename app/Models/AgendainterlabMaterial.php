<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;

class AgendainterlabMaterial extends Model
{
    use LogsModelActivity, SetDefaultUid;

    protected $table = 'agendainterlab_materiais';

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

    public function agendainterlab(): BelongsTo
    {
        return $this->belongsTo(AgendaInterlab::class);
    }
}
