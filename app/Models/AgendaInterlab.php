<?php

namespace App\Models;

use App\Enums\InterlabLotePostagemStatus;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AgendaInterlab extends Model
{
    use LogsActivity, SetDefaultUid;

    protected $table = 'agenda_interlabs';

    protected $guarded = [];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'data_limite_inscricao' => 'date',
        'data_limite_envio_ensaios' => 'date',
        'data_inicio_ensaios' => 'date',
        'data_limite_envio_resultados' => 'date',
        'data_divulgacao_relatorios' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    /**
     * Retorna interlab associado
     */
    public function interlab(): BelongsTo
    {
        return $this->belongsTo(Interlab::class);
    }

    /**
     * Retorna lançamentos de despesa associados
     *
     * @return HasMany<InterlabDespesaLancamento, $this>
     */
    public function despesaLancamentos(): HasMany
    {
        return $this->hasMany(InterlabDespesaLancamento::class);
    }

    /**
     * Retorna itens de despesa associados via lançamentos
     *
     * @return HasManyThrough<InterlabDespesa, InterlabDespesaLancamento, $this>
     */
    public function despesas(): HasManyThrough
    {
        return $this->hasManyThrough(
            InterlabDespesa::class,
            InterlabDespesaLancamento::class,
            'agenda_interlab_id',
            'interlab_despesa_lancamento_id'
        );
    }

    /**
     * Retorna avaliações de fornecedores associadas via lançamentos
     *
     * @return HasManyThrough<FornecedorAvaliacao, InterlabDespesaLancamento, $this>
     */
    public function fornecedorAvaliacoes(): HasManyThrough
    {
        return $this->hasManyThrough(
            FornecedorAvaliacao::class,
            InterlabDespesaLancamento::class,
            'agenda_interlab_id',
            'interlab_despesa_lancamento_id'
        );
    }

    /**
     * Retorna rodadas associados
     */
    public function rodadas(): HasMany
    {
        return $this->hasMany(InterlabRodada::class);
    }

    /**
     * Retorna inscritos associados
     */
    public function inscritos(): HasMany
    {
        return $this->hasMany(InterlabInscrito::class, 'agenda_interlab_id', 'id');
    }

    /**
     * @return HasMany<InterlabLotePostagem, $this>
     */
    public function lotesPostagem(): HasMany
    {
        return $this->hasMany(InterlabLotePostagem::class);
    }

    public function lotePostagemAtivo(): ?InterlabLotePostagem
    {
        return $this->lotesPostagem()->emProcessamento()->latest('id')->first();
    }

    public function lotePostagemMaisRecenteComErro(): ?InterlabLotePostagem
    {
        $lote = $this->lotesPostagem()->latest('id')->first();

        if ($lote === null || $lote->status !== InterlabLotePostagemStatus::Erro) {
            return null;
        }

        return $lote;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, InterlabLotePostagem>
     */
    public function lotesPostagemConcluidos(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->lotesPostagem()
            ->where('status', InterlabLotePostagemStatus::Concluido)
            ->with(['etiquetas', 'itens'])
            ->latest('id')
            ->get();
    }

    /**
     * Retorna materiais associados
     */
    public function materiais(): HasMany
    {
        return $this->hasMany(AgendainterlabMaterial::class);
    }

    /**
     * Retorna valores associados
     */
    public function valores(): HasMany
    {
        return $this->hasMany(AgendaInterlabValor::class);
    }

    /**
     * Retorna analistas vinculados a esta agenda
     */
    public function analistas(): HasMany
    {
        return $this->hasMany(InterlabAnalista::class);
    }

    /**
     * Exportação XLS deve gerar uma linha por analista (TAG e nome do analista)
     * quando o PEP é por analista.
     */
    public function exportaInscritosPorAnalista(): bool
    {
        $this->loadMissing('interlab');

        return ($this->interlab->avaliacao ?? null) === 'ANALISTA';
    }
}
