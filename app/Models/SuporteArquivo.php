<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SuporteArquivo extends Model
{
    use HasFactory;
    use LogsActivity;
    use SetDefaultUid;
    use SoftDeletes;

    protected $table = 'suporte_arquivos';

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->useLogName('Suporte — arquivos');
    }

    /** @return BelongsTo<SuporteDemanda, $this> */
    public function demanda(): BelongsTo
    {
        return $this->belongsTo(SuporteDemanda::class, 'demanda_id');
    }

    /** @return BelongsTo<SuporteAtividade, $this> */
    public function atividade(): BelongsTo
    {
        return $this->belongsTo(SuporteAtividade::class, 'atividade_id');
    }

    public static function novoRascunho(?SuporteDemanda $demanda, ?SuporteAtividade $atividade = null, bool $novaAtividade = false): string
    {
        $token = (string) Str::uuid();
        session()->put('suporte.rascunhos.'.$token, [
            'demanda_id' => $demanda?->id,
            'atividade_id' => $atividade?->id,
            'nova_atividade' => $novaAtividade,
            'criado_em' => now()->timestamp,
        ]);

        return $token;
    }

    /** @return array{demanda_id: ?int, atividade_id: ?int, nova_atividade: bool, criado_em: int} */
    public static function autorizarRascunho(string $token, User $user): array
    {
        SuporteDemanda::autorizarAcesso($user);
        $contexto = session('suporte.rascunhos.'.$token);
        abort_unless(is_array($contexto) && $contexto['criado_em'] > now()->subDay()->timestamp, 404);
        if ($contexto['demanda_id']) {
            $demanda = SuporteDemanda::query()->findOrFail($contexto['demanda_id']);
            if (! $contexto['nova_atividade']) {
                $demanda->exigirAberta();
            }
            if ($contexto['atividade_id']) {
                $atividade = $demanda->atividades()->findOrFail($contexto['atividade_id']);
                abort_unless($atividade->podeEditar($user), 403);
            }
        }

        return $contexto;
    }
}
