<?php

namespace App\Livewire\Interlab;

use App\Actions\Interlab\CriarInterlabLotePostagemAction;
use App\Actions\Interlab\DestinatarioInterlabSnapshot;
use App\Livewire\Forms\LotePostagemForm;
use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;
use App\Models\InterlabLotePostagem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class GerarLotePostagem extends Component
{
    public AgendaInterlab $agenda;

    public LotePostagemForm $form;

    /** @var list<int|string> */
    public array $inscritosSelecionados = [];

    #[Locked]
    public ?string $loteEmProcessamentoUid = null;

    #[Locked]
    public ?string $loteErroDispensadoUid = null;

    public function mount(AgendaInterlab $agenda): void
    {
        $this->agenda = $agenda;
        $this->form->setNew();
    }

    public function dispensarErro(string $loteUid): void
    {
        $this->loteErroDispensadoUid = $loteUid;
    }

    /**
     * @param  list<array{conteudo: string, quantidade: string, valor_unitario: string}>|null  $itensDeclaracao
     */
    public function gerarPostagem(?array $itensDeclaracao = null): void
    {
        $this->resetValidation();

        if ($itensDeclaracao !== null) {
            $this->form->itensDeclaracao = array_values($itensDeclaracao);
        }

        try {
            $this->form->validate();
            $ids = $this->validarSelecao();

            app(CriarInterlabLotePostagemAction::class)->execute($this->agenda, $ids, $this->form->toParametros());
        } catch (ValidationException $exception) {
            $this->interromperComValidacao($exception);

            return;
        }

        $this->inscritosSelecionados = [];
        $this->form->setNew();
        $this->agenda->unsetRelation('lotesPostagem');

        $this->dispatch('show-success-alert', message: 'Lote de postagem criado.');
    }

    public function render(): View
    {
        $loteAtivo = $this->loteComItens($this->agenda->lotePostagemAtivo());
        $loteComErro = $loteAtivo === null ? $this->agenda->lotePostagemMaisRecenteComErro() : null;

        if ($loteComErro?->uid === $this->loteErroDispensadoUid) {
            $loteComErro = null;
        }

        return view('livewire.interlab.gerar-lote-postagem', [
            'inscritos' => $this->inscritosDaAgenda(),
            'loteAtivo' => $loteAtivo,
            'loteComErro' => $loteComErro,
        ]);
    }

    public function rendered(View $view): void
    {
        ['loteAtivo' => $loteAtivo, 'loteComErro' => $loteComErro] = $view->getData();

        if ($loteComErro !== null && $loteComErro->uid === $this->loteEmProcessamentoUid) {
            $this->dispatch('show-error-alert', message: "Não foi possível concluir a postagem do lote {$loteComErro->nome}.");
        }

        $this->loteEmProcessamentoUid = $loteAtivo?->uid;
    }

    private function interromperComValidacao(ValidationException $exception): void
    {
        $erros = $exception->errors();
        $this->setErrorBag($erros);

        Log::channel('validation')->info('Erro de validação ao gerar lote de postagem', [
            'caminho' => request()->path(),
            'campos' => array_keys($erros),
            'quantidade' => array_sum(array_map(count(...), $erros)),
        ]);

        $mensagem = match (true) {
            isset($erros['cepDestinatarios']) => 'Nenhum lote foi gerado. Revise os CEPs indicados.',
            isset($erros['consultaCep']) => 'Nenhum lote foi gerado. '.$erros['consultaCep'][0],
            default => $exception->validator->errors()->first() ?: 'Não foi possível gerar a postagem.',
        };

        $this->dispatch('show-error-alert', message: $mensagem);
    }

    /**
     * @return list<int>
     */
    private function validarSelecao(): array
    {
        $ids = array_values(array_unique(array_map(intval(...), $this->inscritosSelecionados)));

        if ($ids === []) {
            throw ValidationException::withMessages([
                'inscritosSelecionados' => 'Selecione ao menos um laboratório.',
            ]);
        }

        $inscritos = InterlabInscrito::query()
            ->where('agenda_interlab_id', $this->agenda->id)
            ->whereIn('id', $ids)
            ->whereNotNull('laboratorio_id')
            ->with(['laboratorio.endereco'])
            ->get();

        if ($inscritos->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'inscritosSelecionados' => 'A seleção contém inscrições que não pertencem a esta agenda.',
            ]);
        }

        $laboratorios = $inscritos->pluck('laboratorio_id');

        if ($laboratorios->unique()->count() !== $laboratorios->count()) {
            throw ValidationException::withMessages([
                'inscritosSelecionados' => 'Um laboratório só pode ser selecionado uma vez no mesmo lote.',
            ]);
        }

        $mensagens = $inscritos
            ->flatMap(fn (InterlabInscrito $inscrito): array => $this->mensagensDeDestinatario($inscrito))
            ->all();

        if ($mensagens !== []) {
            throw ValidationException::withMessages([
                'inscritosSelecionados' => $mensagens,
            ]);
        }

        return $ids;
    }

    /**
     * @return list<string>
     */
    private function mensagensDeDestinatario(InterlabInscrito $inscrito): array
    {
        return app(DestinatarioInterlabSnapshot::class)->mensagens($inscrito);
    }

    private function loteComItens(?InterlabLotePostagem $lote): ?InterlabLotePostagem
    {
        if ($lote === null) {
            return null;
        }

        $lote->load('itens');
        $lote->itens->each->setRelation('lotePostagem', $lote);

        return $lote;
    }

    /**
     * @return Collection<int, InterlabInscrito>
     */
    private function inscritosDaAgenda(): Collection
    {
        return InterlabInscrito::query()
            ->where('agenda_interlab_id', $this->agenda->id)
            ->whereNotNull('laboratorio_id')
            ->with([
                'laboratorio.endereco',
                'laboratorio.empresa:id,nome_razao',
            ])
            ->get()
            ->sortBy(fn (InterlabInscrito $inscrito): string => mb_strtolower((string) $inscrito->laboratorio?->nome))
            ->values();
    }
}
