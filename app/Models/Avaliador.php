<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\SetDefaultUid;


class Avaliador extends Model
{
    use LogsActivity, SetDefaultUid;

    protected $table = 'avaliadores';

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
            ->useLogName(get_class($this));
    }


    protected $casts = [
        'data_ingresso'  => 'date',
    ];


    /**
     * Carrega pessoa
     * @return BelongsTo
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Endereço pessoal
     * @return BelongsTo
     */
    public function enderecoPessoal(): BelongsTo
    {
        return $this->belongsTo(Endereco::class, 'endereco_pessoal_id');
    }

    /**
     * Endereço comercial
     * @return BelongsTo
     */
    public function enderecoComercial(): BelongsTo
    {
        return $this->belongsTo(Endereco::class, 'endereco_comercial_id');
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
     * Status
     * @return HasMany
     */
    public function status(): HasMany
    {
        return $this->hasMany(StatusAvaliador::class);
    }
}
