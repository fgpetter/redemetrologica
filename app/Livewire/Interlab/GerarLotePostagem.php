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
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class GerarLotePostagem extends Component
{
    public AgendaInterlab $agenda;

    public LotePostagemForm $form;

    /** @var list<int|string> */
    public array $inscritosSelecionados = [];

    public bool $erroDispensado = false;

    public function mount(AgendaInterlab $agenda): void
    {
        $this->agenda = $agenda;
        $this->form->setNew();
    }

    public function dispensarErro(): void
    {
        $this->erroDispensado = true;
    }

    /**
     * @param  list<array{conteudo: string, quantidade: string, valor_unitario: string}>|null  $itensDeclaracao
     */
    public function gerarPostagem(?array $itensDeclaracao = null): void
    {
        if ($itensDeclaracao !== null) {
            $this->form->itensDeclaracao = array_values($itensDeclaracao);
        }

        try {
            $this->form->validate();
            $ids = $this->validarSelecao();
        } catch (ValidationException $exception) {
            $this->interromperComValidacao($exception);

            return;
        }

        app(CriarInterlabLotePostagemAction::class)->execute(
            $this->agenda,
            $ids,
            $this->form->toParametros(),
        );

        $this->inscritosSelecionados = [];
        $this->form->setNew();
        $this->agenda->unsetRelation('lotesPostagem');

        $this->dispatch('show-success-alert', message: 'Lote de postagem criado.');
    }

    public function render(): View
    {
        $loteAtivo = $this->loteComItens($this->agenda->lotePostagemAtivo());
        $loteComErro = null;

        if ($loteAtivo === null && ! $this->erroDispensado) {
            $loteComErro = $this->loteComItens($this->agenda->lotePostagemMaisRecenteComErro());
        }

        return view('livewire.interlab.gerar-lote-postagem', [
            'inscritos' => $this->inscritosDaAgenda(),
            'loteAtivo' => $loteAtivo,
            'loteComErro' => $loteComErro,
        ]);
    }

    private function interromperComValidacao(ValidationException $exception): void
    {
        foreach ($exception->errors() as $campo => $mensagens) {
            foreach ($mensagens as $mensagem) {
                $this->addError($campo, $mensagem);
            }
        }

        $mensagem = collect($exception->errors())->flatten()->first();

        $this->dispatch(
            'show-error-alert',
            message: is_string($mensagem) ? $mensagem : 'Não foi possível gerar a postagem.',
        );
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
