<?php

namespace App\Models;

use App\Casts\TelefoneCast;
use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;

class InterlabAnalista extends Model
{
    use LogsModelActivity, SetDefaultUid;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'interlab_analistas';

    protected $casts = [
        'senha_enviada' => 'datetime',
        'telefone' => TelefoneCast::class,
    ];

    protected function nome(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : mb_strtoupper($value, 'UTF-8'),
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(get_class($this));
    }

    public function interlabInscrito(): BelongsTo
    {
        return $this->belongsTo(InterlabInscrito::class);
    }
}
