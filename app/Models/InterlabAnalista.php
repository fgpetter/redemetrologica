<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InterlabAnalista extends Model
{
    use LogsActivity, SetDefaultUid;

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
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    protected function telefone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->formataTel($value),
            set: fn (?string $value) => preg_replace("/[^\d]/", '', $value),
        );
    }

    protected function formataTel(?string $value): string
    {
        if (strlen($value) === 11) {
            return preg_replace('/([0-9]{2})([0-9]{5})([0-9]{4})/', '($1) $2-$3', $value);
        }
        if (strlen($value) === 10) {
            return preg_replace('/([0-9]{2})([0-9]{4})([0-9]{4})/', '($1) $2-$3', $value);
        }

        return '';
    }

    public function interlabInscrito(): BelongsTo
    {
        return $this->belongsTo(InterlabInscrito::class);
    }
}
