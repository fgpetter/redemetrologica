<?php

namespace App\Models;

use App\Enums\SuporteClassificacao;
use App\Enums\SuporteStatus;
use App\Traits\LogsModelActivity;
use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Support\LogOptions;

class SuporteDemanda extends Model
{
    use HasFactory;
    use LogsModelActivity;
    use SetDefaultUid;
    use SoftDeletes;

    protected $table = 'suporte_demandas';

    protected $guarded = [];

    protected $casts = ['status' => SuporteStatus::class, 'resolvido_em' => 'datetime', 'classificacao' => SuporteClassificacao::class, 'horas_gastas' => 'integer'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->useLogName('Suporte');
    }

    /** @return BelongsTo<User, $this> */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'suporte_demanda_user', 'demanda_id')->withTimestamps();
    }

    /** @return HasMany<SuporteAtividade, $this> */
    public function atividades(): HasMany
    {
        return $this->hasMany(SuporteAtividade::class, 'demanda_id');
    }

    /** @return HasMany<SuporteArquivo, $this> */
    public function arquivos(): HasMany
    {
        return $this->hasMany(SuporteArquivo::class, 'demanda_id')->whereNull('atividade_id');
    }

    public static function autorizarAcesso(?User $user): void
    {
        abort_unless($user?->hasPermissionTo(['admin', 'funcionario']), 404);
    }

    public function exigirAberta(): void
    {
        if ($this->status->encerrada()) {
            throw ValidationException::withMessages(['suporte' => 'Reabra a demanda para editar o conteúdo.']);
        }
    }

    public function temPendencia(): bool
    {
        return $this->pendencia_versao > $this->resolvida_versao;
    }

    public static function bloquearFila(): void
    {
        DB::table('suporte_controle')->where('id', 1)->lockForUpdate()->first();
    }

    /** @param array{busca?: string, status?: string} $filtros */
    public static function comFiltros(array $filtros): Builder
    {
        return self::query()
            ->when($filtros['busca'] ?? '', fn (Builder $query, string $busca) => $query->where('titulo', 'like', '%'.$busca.'%'))
            ->when(($filtros['status'] ?? 'ativas') === 'ativas', fn (Builder $query) => $query->whereNotIn('status', ['concluida', 'cancelada']))
            ->when(! in_array($filtros['status'] ?? 'ativas', ['ativas', 'todos'], true), fn (Builder $query) => $query->where('status', $filtros['status']));
    }

    public function alterarStatus(User $user, SuporteStatus $status): void
    {
        self::autorizarAcesso($user);
        DB::transaction(function () use ($status): void {
            self::bloquearFila();
            $demanda = self::query()->lockForUpdate()->findOrFail($this->id);
            if ($status === SuporteStatus::EmAndamento) {
                $outra = self::query()->whereKeyNot($demanda->id)->where('status', $status)->first();
                if ($outra) {
                    throw ValidationException::withMessages(['status' => "A demanda #{$outra->id} — {$outra->titulo} já está em andamento."]);
                }
            }
            $demanda->update(['status' => $status]);
        });
        $this->refresh();
    }

    public function resolverPendencia(User $user, int $versao): void
    {
        self::autorizarAcesso($user);
        abort_unless($user->hasPermissionTo('admin'), 403);
        DB::transaction(function () use ($user, $versao): void {
            $demanda = self::query()->lockForUpdate()->findOrFail($this->id);
            $demanda->update([
                'resolvida_versao' => max($demanda->resolvida_versao, min($versao, $demanda->pendencia_versao)),
                'resolvido_por' => $user->id,
                'resolvido_em' => now(),
            ]);
        });
    }

    public function excluir(User $user): void
    {
        self::autorizarAcesso($user);
        abort_unless($user->hasPermissionTo('admin'), 403);
        DB::transaction(function (): void {
            self::bloquearFila();
            $demanda = self::query()->lockForUpdate()->findOrFail($this->id);
            $demanda->exigirAberta();
            $demanda->atividades()->get()->each->delete();
            SuporteArquivo::query()->where('demanda_id', $demanda->id)->get()->each->delete();
            $demanda->delete();
        });
    }
}
