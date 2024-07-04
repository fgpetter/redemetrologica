<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Avaliador extends Model
{
    use HasFactory;

    protected $table = 'avaliadores';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'data_ingresso'  => 'date',
    ];


    /**
     * Carrega pessoa
     * @return BelongsTo
     */
    public function pessoa() : BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Qualificações
     * @return HasMany
     */
    public function qualificacoes(): HasMany
    {
        return $this->hasMany(Qualificacao::class);
    }

    /**
     * Áreas de atuação
     * @return HasMany
     */
    public function areas(): HasMany
    {
        return $this->hasMany(AvaliadorArea::class);
    }

    /**
     * Certificados
     * @return HasMany
     */
    public function certificados(): HasMany
    {
        return $this->hasMany(CertificadoAvaliador::class);
    }



}
