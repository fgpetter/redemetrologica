<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Qualificacao extends Model
{
    use LogsActivity;

    protected $table = 'qualificacoes';

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
    public function avaliador() : BelongsTo
    {
        return $this->belongsTo(Avaliador::class);
    }


}
