<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Pessoa extends Model
{
    use SoftDeletes, LogsActivity;

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

    /**
     * Retorna usuário da pessoa
     * @return HasOne
     */

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Retorna empresa a qual a pessoa pertence
     * @return BelongsToMany
     */

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Pessoa::class, 'empresas_pessoas', 'pessoa_id', 'empresa_id');
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(CursoInscrito::class);
    }

    public function interlabs(): HasMany
    {
        return $this->hasMany(InterlabInscrito::class);
    }

    // Acessors and mutators

    protected function cpfCnpj(): Attribute
    {
        return Attribute::make(
            get: fn (string|null $value) => $this->formataDoc($value),
            set: fn (string|null $value) => preg_replace("/[^\d]/", "", $value),
        );
    }

    protected function telefone(): Attribute
    {
        return Attribute::make(
            get: fn (string|null $value) => $this->formataTel($value),
            set: fn (string|null $value) => preg_replace("/[^\d]/", "", $value),
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
            return preg_replace("/([0-9]{2})([0-9]{5})([0-9]{4})/", "(\$1) \$2-\$3", $value);
        }
        if(strlen($value) === 10){
            return preg_replace("/([0-9]{2})([0-9]{4})([0-9]{4})/", "(\$1) \$2-\$3", $value);
        }

        return '';

    }

}
