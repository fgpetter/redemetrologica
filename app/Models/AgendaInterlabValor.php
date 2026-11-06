<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;

class AgendaInterlabValor extends Model
{
    use HasFactory, LogsModelActivity, SetDefaultUid;

    protected $table = 'agendainterlab_valores';

    protected $guarded = [];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_assoc' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(self::class);
    }

    public function agendaInterlab(): BelongsTo
    {
        return $this->belongsTo(AgendaInterlab::class);
    }
}
