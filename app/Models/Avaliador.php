<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Support\LogOptions;

class Avaliador extends Model
{
    use LogsModelActivity, SetDefaultUid;

    protected $table = 'avaliadores';

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

    protected $casts = [
        'data_ingresso' => 'date',
    ];

    /**
     * Carrega pessoa
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Qualificações
     */
    public function qualificacoes(): HasMany
    {
        return $this->hasMany(Qualificacao::class);
    }

    /**
     * Áreas de atuação
     */
    public function areas(): HasMany
    {
        return $this->hasMany(AvaliadorArea::class);
    }

    /**
     * Status
     */
    public function status(): HasMany
    {
        return $this->hasMany(StatusAvaliador::class);
    }
}
