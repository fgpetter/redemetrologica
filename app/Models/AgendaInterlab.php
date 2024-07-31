<?php

namespace App\Models;

use App\Models\Interlab;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgendaInterlab extends Model
{
    use HasFactory;

    protected $table = 'agenda_interlabs';

    protected $guarded = [];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    /**
     * Retorna interlab associado
     *
     * @return BelongsTo
     */
    public function interlab(): BelongsTo
    {
        return $this->belongsTo(Interlab::class);
    }

    /**
     * Retorna materiais padrão
     *
     * @return HasMany
     */
    public function despesa(): HasMany
    {
        return $this->hasMany(InterlabDespesa::class);
    }

}
