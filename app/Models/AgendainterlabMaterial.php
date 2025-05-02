<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgendainterlabMaterial extends Model
{
    use HasFactory, SetDefaultUid;

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
            ->useLogName(get_class($this));
    }

    public function agendainterlab(): BelongsTo
    {
        return $this->belongsTo(AgendaInterlab::class);
    }
}