<?php

namespace App\Models;

use App\Helpers\Telefone;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Laboratorio extends Model
{
    use LogsActivity, SetDefaultUid;

    protected $table = 'laboratorios';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected function telefone(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => Telefone::somenteNumeros($value),
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    /**
     * Carrega pessoa
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Carrega endereço
     */
    public function endereco(): BelongsTo
    {
        return $this->belongsTo(Endereco::class);
    }

    /**
     * Cursos habilitados
     */
    public function laboratoriosInternos(): HasMany
    {
        return $this->hasMany(LaboratorioInterno::class);
    }
}
