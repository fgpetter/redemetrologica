<?php

namespace App\Livewire\Avaliacoes;

use App\Actions\Avaliacoes\GerarPdfMediaAvaliacoesAction;
use App\Models\AgendaAvaliacao;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaAvaliacoesTable extends Component
{
    use WithPagination;

    #[Url(as: 'p', history: false)]
    public $perPage = 10;

    #[Url(as: 'di', history: false)]
    public $dataIni = '';

    #[Url(as: 'df', history: false)]
    public $dataFim = '';

    #[Url(as: 'q', history: false)]
    public $search = '';

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['perPage', 'dataIni', 'dataFim', 'search'])) {
            $this->resetPage();
        }
    }

    public function limpar(): void
    {
        $this->reset(['dataIni', 'dataFim', 'search']);
        $this->resetPage();
    }

    public function imprimir(): BinaryFileResponse
    {
        return app(GerarPdfMediaAvaliacoesAction::class)->execute(
            $this->dataIni,
            $this->dataFim,
            $this->search,
        );
    }

    protected function getQuery()
    {
        return AgendaAvaliacao::query()
            ->with(['laboratorio', 'laboratorioInterno', 'pesquisa'])
            ->when($this->dataIni, fn ($query, $date) => $query->where('data_inicio', '>=', $date))
            ->when($this->dataFim, fn ($query, $date) => $query->where('data_inicio', '<=', $date))
            ->when($this->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('laboratorio', fn ($q) => $q->where('nome_laboratorio', 'like', "%{$search}%"))
                        ->orWhereHas('laboratorioInterno', fn ($q) => $q->where('nome', 'like', "%{$search}%"));
                });
            })
            ->orderBy('data_inicio');
    }

    public function render()
    {
        $avaliacoes = $this->getQuery()->paginate($this->perPage);

        $grupos = $avaliacoes->getCollection()->groupBy(function (AgendaAvaliacao $avaliacao) {
            return $avaliacao->data_inicio
                ? Carbon::parse($avaliacao->data_inicio)->format('m/Y')
                : '';
        });

        return view('livewire.avaliacoes.media-avaliacoes-table', [
            'avaliacoes' => $avaliacoes,
            'grupos' => $grupos,
        ]);
    }
}
