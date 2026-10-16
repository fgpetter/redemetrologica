<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SuporteAtividade extends Model
{
    use HasFactory;
    use LogsActivity;
    use SetDefaultUid;
    use SoftDeletes;

    protected $table = 'suporte_atividades';

    protected $guarded = [];

    protected $casts = ['apos_encerramento' => 'boolean'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->useLogName('Suporte — atividades');
    }

    /** @return BelongsTo<SuporteDemanda, $this> */
    public function demanda(): BelongsTo
    {
        return $this->belongsTo(SuporteDemanda::class, 'demanda_id');
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<SuporteAtividade, $this> */
    public function respostas(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at')->orderBy('id');
    }

    /** @return HasMany<SuporteArquivo, $this> */
    public function arquivos(): HasMany
    {
        return $this->hasMany(SuporteArquivo::class, 'atividade_id');
    }

    public function podeEditar(User $user): bool
    {
        return $user->hasPermissionTo('admin') || $this->owner_id === $user->id;
    }

    public function excluir(User $user): void
    {
        SuporteDemanda::autorizarAcesso($user);
        abort_unless($user->hasPermissionTo('admin'), 403);
        DB::transaction(function (): void {
            $demanda = SuporteDemanda::query()->lockForUpdate()->findOrFail($this->demanda_id);
            $demanda->exigirAberta();
            $atividade = $demanda->atividades()->findOrFail($this->id);
            $ids = $atividade->respostas()->pluck('id')->push($atividade->id);
            SuporteArquivo::query()->whereIn('atividade_id', $ids)->get()->each->delete();
            $demanda->atividades()->whereIn('id', $ids)->get()->each->delete();
            $demanda->touch();
        });
    }
}
