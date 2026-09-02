<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AvaliacaoPesquisa extends Model
{
    use LogsActivity, SetDefaultUid;

    protected $table = 'avaliacao_pesquisas';

    protected $guarded = [];

    protected $attributes = [
        'conferida' => false,
    ];

    protected $casts = [
        'comentarios_avaliadores' => 'array',
        'preenchido_em' => 'datetime',
        'conferida' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    /**
     * Avaliação vinculada à pesquisa.
     */
    public function avaliacao(): BelongsTo
    {
        return $this->belongsTo(AgendaAvaliacao::class, 'agenda_avaliacao_id');
    }
}
