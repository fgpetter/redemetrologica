<?php

namespace App\Models;

use App\Casts\TelefoneCast;
use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Support\LogOptions;

class InterlabInscrito extends Model
{
    use LogsModelActivity, SetDefaultUid;

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

    // cast data inscrição as date and valor as money BRL
    protected $casts = [
        'data_inscricao' => 'date',
        'senha_enviada' => 'datetime',
        'telefone' => TelefoneCast::class,
    ];

    public static function certificadosPendentes(): Builder
    {
        return static::query()
            ->whereNull('certificado_emitido')
            ->whereHas('agendaInterlab', fn (Builder $query) => $query->where('status', 'CONCLUIDO'))
            ->whereHas('lancamentoFinanceiro', fn (Builder $query) => $query->where('status', 'EFETIVADO'));
    }

    public function getActivitylogOptions(): LogOptions
    {
        $options = LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(get_class($this));

        return $options;
    }

    /**
     * Agenda interlab da inscrição
     */
    public function agendaInterlab(): BelongsTo
    {
        return $this->belongsTo(AgendaInterlab::class);
    }

    /**
     * Pessoa que realizou a inscrição
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Empresa relacionada a inscrição, para cobrança
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'empresa_id', 'id');
    }

    /**
     * Laboratório inscrito no PEP
     *
     * @return BelongsTo
     */
    public function laboratorio(): HasOne
    {
        return $this->hasOne(InterlabLaboratorio::class, 'id', 'laboratorio_id');
    }

    /**
     * Pessoa inscrita no PEP
     */
    public function pessoaInscrita(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'empresa_id', 'id');
    }

    public function getTagSenhaDocAttribute()
    {
        return DadosGeraDoc::where('tipo', 'tag_senha')
            ->whereJsonContains('content->participante_id', $this->id)
            ->first();
    }

    /**
     * Lançamento financeiro desta inscrição
     */
    public function lancamentoFinanceiro(): BelongsTo
    {
        return $this->belongsTo(LancamentoFinanceiro::class, 'lancamento_financeiro_id');
    }

    /**
     * Verifica se o pagamento foi confirmado (baixado)
     */
    public function getIsPagoAttribute(): bool
    {
        return $this->lancamentoFinanceiro?->status === 'EFETIVADO';
    }

    /**
     * Verifica se o certificado já foi emitido
     */
    public function getIsCertificadoEmitidoAttribute(): bool
    {
        return ! empty($this->certificado_emitido);
    }

    /**
     * Analistas vinculados a esta inscrição
     */
    public function analistas(): HasMany
    {
        return $this->hasMany(InterlabAnalista::class, 'interlab_inscrito_id');
    }
}
