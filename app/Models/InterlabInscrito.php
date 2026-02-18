<?php

namespace App\Models;

use App\Models\User;
use App\Models\DadosGeraDoc;
use App\Traits\SetDefaultUid;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;



class InterlabInscrito extends Model
{

    use LogsActivity, SetDefaultUid;

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
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $options = LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));

        if (session('impersonator_id')) {
            $impersonator = User::find(session('impersonator_id'));
            $options->setDescriptionForEvent(function(string $eventName) use ($impersonator) {
                return "{$eventName} impersonated by {$impersonator->name}";
            });
        }

        return $options;
    }


    /**
     * Agenda interlab da inscrição
     * @return BelongsTo
     */
    public function agendaInterlab() : BelongsTo
    {
        return $this->belongsTo(AgendaInterlab::class);
    }

    /**
     * Pessoa que realizou a inscrição
     * @return BelongsTo
     */
    public function pessoa() : BelongsTo
    {
        return $this->belongsTo(Pessoa::class);
    }

    /**
     * Empresa relacionada a inscrição, para cobrança
     * @return BelongsTo
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'empresa_id', 'id');
    }

    /**
     * Laboratório inscrito no PEP
     * @return BelongsTo
     */
    public function laboratorio() : HasOne
    {
        return $this->hasOne(InterlabLaboratorio::class, 'id', 'laboratorio_id');
    }

    /**
     * Pessoa inscrita no PEP
     * @return BelongsTo
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
     * @return BelongsTo
     */
    public function lancamentoFinanceiro(): BelongsTo
    {
        return $this->belongsTo(LancamentoFinanceiro::class, 'lancamento_financeiro_id');
    }

    /**
     * Gera a tag senha para o inscrito
     * @return string
     */
    public static function geraTagSenha(AgendaInterlab $agendaInterlab): string
    {
        $tag = $agendaInterlab->interlab->tag ?? throw new \Exception('Tag do interlab não encontrada');
        $senha = $tag . '-' . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        while (
            self::where('tag_senha', $senha)
                ->where('agenda_interlab_id', $agendaInterlab->id)
            ->exists()
        ) {
            $senha = $tag . '-' . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        }
        
        return $senha;
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
        return !empty($this->certificado_emitido);
    }

    /**
     * Analistas vinculados a esta inscrição
     * @return HasMany
     */
    public function analistas(): HasMany
    {
        return $this->hasMany(InterlabAnalista::class, 'interlab_inscrito_id');
    }
}