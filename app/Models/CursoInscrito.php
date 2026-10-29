<?php

namespace App\Models;

use App\Casts\TelefoneCast;
use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Support\LogOptions;

class CursoInscrito extends Model
{
    use LogsModelActivity, SetDefaultUid;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'telefone' => TelefoneCast::class,
    ];

    public static function certificadosPendentes(): Builder
    {
        return static::query()
            ->whereNull('certificado_emitido')
            ->whereHas('lancamentoFinanceiro', fn (Builder $query) => $query->where('status', 'EFETIVADO'))
            ->whereHas('agendaCurso', function (Builder $query): void {
                $query->where('status', 'REALIZADO')
                    ->where(function (Builder $query): void {
                        $query->whereNull('tipo_agendamento')->orWhere('tipo_agendamento', '!=', 'IN-COMPANY');
                    });
            });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName(get_class($this));

    }

    /**
     * Carrega curso
     */
    public function agendaCurso(): BelongsTo
    {
        return $this->belongsTo(AgendaCursos::class);
    }

    /**
     * Carrega pessoa
     */
    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class)->withTrashed();
    }

    /**
     * Carrega Empresa associado
     */
    public function empresa(): HasOne
    {
        return $this->hasOne(Pessoa::class, 'id', 'empresa_id');
    }

    /**
     * Carrega lancamento financeiro
     */
    public function lancamentoFinanceiro(): BelongsTo
    {
        return $this->belongsTo(LancamentoFinanceiro::class);
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
}
