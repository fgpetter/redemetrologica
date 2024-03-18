<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pessoa extends Model
{
    use
        HasFactory,
        SoftDeletes;

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
     * Retorna quando essa pessoa é um funcionário.
     */
    public function funcionario(): HasOne
    {
        return $this->hasOne(Funcionario::class);
    }

    /**
     * Retorna quando essa pessoa é um avaliador.
     */
    public function avaliador(): HasOne
    {
        return $this->hasOne(Avaliador::class);
    }

    /**
     * Retorna quando essa pessoa é um instrutor.
     */
    public function instrutor(): HasOne
    {
        return $this->hasOne(Instrutor::class);
    }

    // Acessors and mutators

    protected function cpfCnpj(): Attribute
    {
        return Attribute::make(
            get: fn (string|null $value) => $this->formataDoc($value),
        );
    }

    protected function telefone(): Attribute
    {
        return Attribute::make(
            get: fn (string|null $value) => $this->formataTel($value),
        );
    }

    protected function formataDoc(string|null $value): string
    {
        if(strlen($value) === 11){
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{2})/", "\$1.\$2.\$3-\$4", $value);
        } else {
            return preg_replace("/([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{2})/", "\$1.\$2.\$3/\$4-\$5", $value);
        }
    }

    protected function formataTel(string|null $value): string
    {
        if(strlen($value) === 11){
            return preg_replace("/([0-9]{2})([0-9]{5})([0-9]{4})/", "\(\$1\) \$2-\$3", $value);
        }
        if(strlen($value) === 10){
            return preg_replace("/([0-9]{2})([0-9]{4})([0-9]{4})/", "\(\$1\) \$2-\$3", $value);
        }

        return '';

    }

}
