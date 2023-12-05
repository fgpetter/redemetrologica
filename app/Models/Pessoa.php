<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pessoa extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * Lista os endereços de uma pessoa.
     */
    public function enderecos(): HasMany
    {
        return $this->hasMany(Endereco::class);
    }

    /**
     * Lista os endereços de uma pessoa.
     */
    public function unidades(): HasMany
    {
        return $this->hasMany(Unidade::class)->with('endereco');
    }

    /**
     * Lista dados bancarios de uma pessoa.
     */
    public function dadosBancarios(): HasMany
    {
        return $this->hasMany(DadoBancario::class);
    }

    /**
     * Lista retorna quando essa pessoa é um funcionário.
     */
    public function funcionario(): HasOne
    {
        return $this->hasOne(Funcionario::class);
    }

    /**
     * Lista retorna quando essa pessoa é um avaliador.
     */
    public function avaliador(): HasOne
    {
        return $this->hasOne(Avaliador::class);
    }

}
