<?php

namespace App\Models;

use App\Casts\TelefoneCast;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CursoInscrito extends Model
{
    use LogsActivity, SetDefaultUid;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'telefone' => TelefoneCast::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));

        if (session('impersonator_id')) {
            $impersonator = User::find(session('impersonator_id'));
            $options->setDescriptionForEvent(function (string $eventName) use ($impersonator) {
                return "{$eventName} impersonated by {$impersonator->name}";
            });
        }
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
