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
     * Retorna despesas associadas
     *
     * @return HasMany
     */
    public function despesas(): HasMany
    {
        return $this->hasMany(InterlabDespesa::class);
    }

    /**
     * Retorna parametros associados
     *
     * @return HasMany
     */
    public function parametros(): HasMany
    {
        return $this->hasMany(InterlabParametro::class);
    }

    /**
     * Retorna rodadas associados
     *
     * @return HasMany
     */
    public function rodadas(): HasMany
    {
        return $this->hasMany(InterlabRodada::class);
    }


}
