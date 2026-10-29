<?php

namespace App\Models;

use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;

class Qualificacao extends Model
{
    use LogsModelActivity, SetDefaultUid;

    protected $table = 'avaliador_qualificacoes';

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
            ->useLogName('Usuários');
    }

    /**
     * Carrega avalidor
     */
    public function avaliador(): BelongsTo
    {
        return $this->belongsTo(Avaliador::class);
    }
}
