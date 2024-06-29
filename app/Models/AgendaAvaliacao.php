<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AgendaAvaliacao extends Model
{
    use HasFactory;

    protected $table = 'agenda_avaliacoes';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Carrega areas avaliadas
     * @return HasMany 
     */
    public function areas(): HasMany
    {
        return $this->hasMany(AreaAvaliada::class, 'avaliacao_id', 'id');
    }

    public function laboratorio(): HasOne
    {
        return $this->hasOne(Laboratorio::class, 'id', 'laboratorio_id');
    }

}
