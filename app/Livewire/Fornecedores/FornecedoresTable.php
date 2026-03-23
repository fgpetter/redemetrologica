<?php

namespace App\Livewire\Fornecedores;

use App\Enums\FornecedorArea;
use App\Models\Fornecedor;
use App\Models\Pessoa;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class FornecedoresTable extends Component
{
    use WithPagination;

    #[Url(as: 'n', history: false)]
    public string $searchNome = '';

    #[Url(as: 'c', history: false)]
    public string $searchCpfCnpj = '';

    #[Url(as: 'a', history: false)]
    public string $area = '';

    #[Url(as: 'p', history: false)]
    public int $perPage = 10;

    #[Url(as: 'sb', history: false)]
    public string $sortBy = 'nome_razao';

    #[Url(as: 'sd', history: false)]
    public string $sortDirection = 'ASC';

    public function setSortBy(string $sortByField): void
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDirection = $this->sortDirection === 'ASC' ? 'DESC' : 'ASC';

            return;
        }
        $this->sortBy = $sortByField;
        $this->sortDirection = 'ASC';
    }

    public function resetFilters(): void
    {
        $this->reset(['searchNome', 'searchCpfCnpj', 'area']);
    }

    public function updatedSearchNome(): void
    {
        $this->resetPage();
    }

    public function updatedSearchCpfCnpj(): void
    {
        $this->resetPage();
    }

    public function updatedArea(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected function getQuery()
    {
        $sortField = $this->sortBy;
        $sortDirection = $this->sortDirection;
        $cpfCnpj = preg_replace('/[^0-9]/', '', $this->searchCpfCnpj);

        $query = Fornecedor::with(['pessoa', 'areas'])
            ->join('pessoas', 'fornecedores.pessoa_id', '=', 'pessoas.id')
            ->select('fornecedores.*')
            ->when($this->searchNome !== '', function ($query) {
                $query->where('pessoas.nome_razao', 'like', '%'.$this->searchNome.'%');
            })
            ->when($cpfCnpj !== '', function ($query) use ($cpfCnpj) {
                $query->where('pessoas.cpf_cnpj', 'like', '%'.$cpfCnpj.'%');
            })
            ->when($this->area !== '', function ($query) {
                $query->whereHas('areas', fn ($q) => $q->where('area', $this->area));
            })
            ->when($sortField, function ($query) use ($sortDirection, $sortField) {
                if ($sortField === 'nome_razao') {
                    $query->orderBy('pessoas.nome_razao', $sortDirection);
                } elseif ($sortField === 'cpf_cnpj') {
                    $query->orderBy('pessoas.cpf_cnpj', $sortDirection);
                } else {
                    $query->orderBy('fornecedores.'.$sortField, $sortDirection);
                }
            });

        return $query;
    }

    public function render()
    {
        $fornecedores = $this->getQuery()->paginate($this->perPage)->withQueryString();

        $pessoas = Pessoa::select('uid', 'nome_razao', 'cpf_cnpj')
            ->whereNotIn('id', function ($query) {
                $query->select('pessoa_id')->from('fornecedores');
            })
            ->get();

        return view('livewire.fornecedores.fornecedores-table', [
            'fornecedores' => $fornecedores,
            'areasEnum' => FornecedorArea::cases(),
            'pessoas' => $pessoas,
        ]);
    }
}
