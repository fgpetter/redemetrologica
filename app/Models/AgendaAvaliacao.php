<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AgendaAvaliacao extends Model
{
    use LogsActivity, SetDefaultUid;

    protected $table = 'agenda_avaliacoes';

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

    /**
     * Carrega areas avaliadas
     */
    public function areas(): HasMany
    {
        return $this->hasMany(AreaAvaliada::class, 'avaliacao_id', 'id');
    }

    public function laboratorio(): HasOne
    {
        return $this->hasOne(Laboratorio::class, 'id', 'laboratorio_id');
    }

    public function tipoAvaliacao(): HasOne
    {
        return $this->hasOne(TipoAvaliacao::class, 'id', 'tipo_avaliacao_id');
    }

    /**
     * Pesquisa de satisfação da avaliação.
     */
    public function pesquisa(): HasOne
    {
        return $this->hasOne(AvaliacaoPesquisa::class);
    }

    /**
     * Laboratório interno da avaliação.
     */
    public function laboratorioInterno(): BelongsTo
    {
        return $this->belongsTo(LaboratorioInterno::class, 'laboratorio_interno_id');
    }
}
