<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;

class Qualificacao extends Model
{
    use LogsActivity, SetDefaultUid;

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
            ->useLogName('Usuários');
    }


    /**
     * Carrega avalidor
     * @return BelongsTo
     */
    public function avaliador(): BelongsTo
    {
        return $this->belongsTo(Avaliador::class);
    }
}
