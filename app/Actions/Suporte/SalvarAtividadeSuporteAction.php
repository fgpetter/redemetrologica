<?php

namespace App\Actions\Suporte;

use App\Models\SuporteAtividade;
use App\Models\SuporteDemanda;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SalvarAtividadeSuporteAction
{
    public function __construct(private ArquivosSuporteAction $arquivos) {}

    /** @param array<int, string> $anexos
     */
    public function execute(User $user, SuporteDemanda $demanda, string $descricao, string $rascunho, array $anexos = [], ?string $parentUid = null, ?string $atividadeUid = null): SuporteAtividade
    {
        SuporteDemanda::autorizarAcesso($user);

        return DB::transaction(function () use ($user, $demanda, $descricao, $rascunho, $anexos, $parentUid, $atividadeUid): SuporteAtividade {
            $demanda = SuporteDemanda::query()->lockForUpdate()->findOrFail($demanda->id);
            Validator::make(['descricao' => $descricao], ['descricao' => ['required', 'string']])->validate();
            $atividade = $atividadeUid ? $demanda->atividades()->where('uid', $atividadeUid)->firstOrFail() : null;
            if ($atividade) {
                $demanda->exigirAberta();
                abort_unless($atividade->podeEditar($user), 403);
            }
            $parent = null;
            if ($parentUid && ! $atividade) {
                $parent = $demanda->atividades()->where('uid', $parentUid)->firstOrFail();
                if ($parent->parent_id !== null) {
                    throw ValidationException::withMessages(['suporte' => 'Respostas não podem receber novas respostas.']);
                }
            }
            $disponiveis = $this->arquivos->disponiveis($user, $rascunho, $demanda, $atividade, ! $atividade);
            $conteudo = SanitizarHtmlSuporteAction::handle($descricao, $disponiveis, 'descricao');
            $nova = ! $atividade;
            if ($nova) {
                $atividade = new SuporteAtividade([
                    'demanda_id' => $demanda->id,
                    'parent_id' => $parent?->id,
                    'owner_id' => $user->id,
                    'apos_encerramento' => $demanda->status->encerrada(),
                ]);
            }
            $atividade->fill(['descricao' => $conteudo['html']])->save();
            $this->arquivos->vincular($disponiveis, $anexos, $conteudo['imagens'], $demanda, $atividade);
            if ($nova) {
                $demanda->participantes()->syncWithoutDetaching([$user->id]);
                if ($demanda->status->encerrada()) {
                    $demanda->pendencia_versao++;
                }
            }
            $demanda->touch();

            return $atividade;
        });
    }
}
