<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;

class Endereco extends Model
{
    use LogsModelActivity, SetDefaultUid;

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

    /**
     * Retorna pessoa do endereço.
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    protected function cep(): Attribute
    {
        return Attribute::make(
            // formatando o CEP para o formato 00000-000
            get: fn (?string $value) => preg_replace('/([0-9]{5})([0-9]{3})/', '$1-$2', $value),
            set: fn (?string $value) => preg_replace("/[^\d]/", '', $value),
        );
    }
}
