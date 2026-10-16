<?php

namespace App\Actions\Suporte;

use App\Enums\SuporteClassificacao;
use App\Enums\SuporteStatus;
use App\Models\SuporteDemanda;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalvarDemandaSuporteAction
{
    public function __construct(private ArquivosSuporteAction $arquivos) {}

    /**
     * @param  array{titulo: string, descricao: string, participantes: array<int, int|string>, classificacao?: ?string, horas_gastas?: ?string}  $dados
     * @param  array<int, string>  $anexos
     */
    public function execute(User $user, ?SuporteDemanda $demanda, array $dados, string $rascunho, array $anexos = [], ?SuporteStatus $status = null): SuporteDemanda
    {
        SuporteDemanda::autorizarAcesso($user);

        $administrativo = $user->hasPermissionTo('admin') ? Validator::make($dados, [
            'classificacao' => ['nullable', Rule::enum(SuporteClassificacao::class)],
            'horas_gastas' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:999999'],
        ])->validate() : [];
        if (array_key_exists('classificacao', $administrativo) && $administrativo['classificacao'] === '') {
            $administrativo['classificacao'] = null;
        }

        return DB::transaction(function () use ($user, $demanda, $dados, $rascunho, $anexos, $status, $administrativo): SuporteDemanda {
            SuporteDemanda::bloquearFila();
            if ($demanda) {
                $demanda = SuporteDemanda::query()->lockForUpdate()->findOrFail($demanda->id);
                $demanda->fill($administrativo);
                if ($demanda->status->encerrada() && (($status && $status !== $demanda->status) || $demanda->isDirty(['classificacao', 'horas_gastas']))) {
                    $demanda->save();
                    if ($status && $status !== $demanda->status) {
                        $demanda->alterarStatus($user, $status);
                    }

                    return $demanda;
                }
                $demanda->exigirAberta();
            }
            $dados = Validator::make($dados, [
                'titulo' => ['required', 'string', 'max:255'],
                'descricao' => ['required', 'string'],
                'participantes' => ['array'],
                'participantes.*' => ['integer', 'distinct', 'exists:users,id'],
            ])->validate();
            $participantes = array_map('intval', $dados['participantes']);
            $permitidos = User::query()->whereIn('id', $participantes)
                ->whereHas('permissions', fn ($query) => $query->whereIn('permission', ['admin', 'funcionario']))->count();
            if ($permitidos !== count($participantes)) {
                throw ValidationException::withMessages(['form.participantes' => 'Selecione apenas admins ou funcionários.']);
            }
            $disponiveis = $this->arquivos->disponiveis($user, $rascunho, $demanda);
            $conteudo = SanitizarHtmlSuporteAction::handle($dados['descricao'], $disponiveis, 'form.descricao');
            $nova = ! $demanda;
            if ($nova) {
                $demanda = new SuporteDemanda([
                    'autor_id' => $user->id,
                    'status' => SuporteStatus::Aberta,
                    'prioridade' => (SuporteDemanda::withTrashed()->max('prioridade') ?? 0) + 1,
                ]);
            }
            $demanda->fill($administrativo);
            $demanda->fill(['titulo' => $dados['titulo'], 'descricao' => $conteudo['html']])->save();
            if ($nova) {
                $participantes[] = $user->id;
            }
            $demanda->participantes()->sync(array_unique($participantes));
            $this->arquivos->vincular($disponiveis, $anexos, $conteudo['imagens'], $demanda);
            if ($status && $status !== $demanda->status) {
                $demanda->alterarStatus($user, $status);
            }

            return $demanda;
        });
    }
}
