<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }


    /**
     * Carrega pessoa
     * @return BelongsTo
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Carrega endereço
     * @return BelongsTo
     */
    public function endereco(): BelongsTo
    {
        return $this->belongsTo(Endereco::class);
    }

    /**
     * Cursos habilitados
     * @return HasMany
     */
    public function laboratoriosInternos(): HasMany
    {
        return $this->hasMany(LaboratorioInterno::class);
    }
}
