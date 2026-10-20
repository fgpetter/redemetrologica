<?php

namespace App\Models;

use App\Casts\TelefoneCast;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LaboratorioInterno extends Model
{
    use LogsActivity, SetDefaultUid;

    protected $table = 'laboratorios_internos';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'telefone' => TelefoneCast::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    /**
     * Carrega pessoa
     */
    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class);
    }

    /**
     * Carrega Empresa associado
     */
    public function area(): HasOne
    {
        return $this->hasOne(AreaAtuacao::class, 'id', 'area_atuacao_id');
    }
}
