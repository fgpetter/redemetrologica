<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;


class InterlabInscrito extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'interlab_inscritos';

    /**
     * Carrega interlab
     * @return BelongsTo
     */
    public function agendaInterlab() : BelongsTo
    {
        return $this->belongsTo(AgendaInterlab::class);
    }

    /**
     * Carrega pessoa
     * @return BelongsTo
     */
    public function pessoa() : BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Carrega Empresa associado
     * @return HasOne
     */
    public function empresa(): HasOne
    {
        return $this->hasOne(Pessoa::class, 'id', 'empresa_id');
    }


}
