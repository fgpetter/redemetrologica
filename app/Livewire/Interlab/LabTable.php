<?php

namespace App\Livewire\Interlab;

use App\Livewire\Forms\LaboratorioInterlabForm;
use App\Models\Endereco;
use App\Models\InterlabLaboratorio;
use App\Models\Pessoa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LabTable extends Component
{
    use WithPagination;

    #[Url(as: 'p', history: false)]
    public $perPage = 15;

    #[Url(as: 's', history: false)]
    public $search = '';

    #[Url(as: 'pep', history: false)]
    public $searchPep = '';

    #[Url(as: 'empresa', history: false)]
    public $empresaSelecionada = '';

    #[Url(as: 'sb', history: false)]
    public $sortBy = 'created_at';

    #[Url(as: 'sd', history: false)]
    public $sortDirection = 'DESC';

    // Modal
    public $showModal = false;

    #[Locked]
    public $isEdit = false;

    // Lab
    #[Locked]
    public $labId;

    public LaboratorioInterlabForm $form;

    public function setSortBy($field)
    {
        if (! in_array($field, ['created_at', 'empresa', 'nome'], true)) {
            return;
        }

        $this->resetPage();

        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'ASC';
        }
    }

    public function resetFilters()
    {
        $this->resetPage();

        $this->reset(['search', 'searchPep', 'empresaSelecionada']);
        $this->dispatch('reset-empresa-filter');
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['search', 'searchPep', 'perPage', 'empresaSelecionada'])) {
            $this->resetPage();
        }
    }

    public function updatedFormCep($value)
    {
        $cep = preg_replace('/\D/', '', $value);

        if (strlen($cep) === 8) {
            $localEndereco = Endereco::where('cep', $cep)
                ->orderBy('id', 'desc')
                ->first();

            if ($localEndereco) {
                $this->form->endereco = $localEndereco->endereco;
                $this->form->bairro = $localEndereco->bairro;
                $this->form->cidade = $localEndereco->cidade;
                $this->form->uf = $localEndereco->uf;
            } else {
                $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

                if ($response->successful() && ! isset($response->json()['erro'])) {
                    $data = $response->json();
                    $this->form->endereco = $data['logradouro'] ?? '';
                    $this->form->bairro = $data['bairro'] ?? '';
                    $this->form->cidade = $data['localidade'] ?? '';
                    $this->form->uf = $data['uf'] ?? '';
                } else {
                    $this->addError('form.cep', 'CEP não encontrado.');
                }
            }
        } elseif (strlen($cep) > 0) {
            $this->addError('form.cep', 'CEP inválido. Certifique-se de que possui 8 dígitos.');
        }
    }

    // Modal CRUD
    public function create()
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->dispatch('open-modal');
    }

    public function edit($uid)
    {
        abort_unless(auth()->user()?->hasPermissionTo(['funcionario', 'admin']), 404);
        $this->resetForm();
        $this->isEdit = true;

        $lab = InterlabLaboratorio::where('uid', $uid)->with('endereco')->firstOrFail();

        $this->labId = $lab->id;
        $this->form->empresa_id = $lab->empresa_id;
        $this->form->nome = $lab->nome;

        if ($lab->endereco) {
            $this->form->cep = $lab->endereco->cep;
            $this->form->endereco = $lab->endereco->endereco;
            $this->form->complemento = $lab->endereco->complemento;
            $this->form->bairro = $lab->endereco->bairro;
            $this->form->cidade = $lab->endereco->cidade;
            $this->form->uf = $lab->endereco->uf;
        }

        $this->dispatch('open-modal');
        $this->dispatch('set-empresa-modal', $this->form->empresa_id);
    }

    public function save()
    {
        abort_unless(auth()->user()?->hasPermissionTo(['funcionario', 'admin']), 404);
        $this->form->validate();

        DB::transaction(function () {

            $enderecoData = [
                'pessoa_id' => $this->form->empresa_id,
                'info' => 'Laboratório Interlab',
                'cep' => $this->form->cep,
                'endereco' => $this->form->endereco,
                'complemento' => $this->form->complemento,
                'bairro' => $this->form->bairro,
                'cidade' => $this->form->cidade,
                'uf' => $this->form->uf,
            ];

            // laboratório
            $lab = $this->isEdit && $this->labId
                ? InterlabLaboratorio::findOrFail($this->labId)
                : new InterlabLaboratorio;

            // endereço
            if ($lab->endereco_id) {
                $endereco = Endereco::updateOrCreate(
                    ['id' => $lab->endereco_id],
                    $enderecoData
                );
            } else {
                $endereco = Endereco::create($enderecoData);
                $lab->endereco_id = $endereco->id;
            }

            $lab->empresa_id = $this->form->empresa_id;
            $lab->nome = $this->form->nome;
            $lab->save();
        });

        $this->dispatch('close-modal');
        $this->dispatch(
            'notify',
            type: 'success',
            content: $this->isEdit ? 'Laboratório atualizado com sucesso!' : 'Laboratório criado com sucesso!'
        );

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['labId', 'isEdit']);
        $this->form->reset();
        $this->dispatch('reset-empresa-modal');
    }

    public function getEmpresasProperty()
    {
        return Pessoa::query()
            ->select(['id', 'cpf_cnpj', 'nome_razao'])
            ->where('tipo_pessoa', 'PJ')
            ->whereIn(
                'id',
                InterlabLaboratorio::query()
                    ->select('empresa_id')
                    ->distinct()
            )
            ->orderBy('nome_razao')
            ->get();
    }

    public function getAllEmpresasProperty()
    {
        return Pessoa::select(['id', 'cpf_cnpj', 'nome_razao'])
            ->where('tipo_pessoa', 'PJ')
            ->orderBy('nome_razao')
            ->get();
    }

    protected function getQuery()
    {
        $query = InterlabLaboratorio::with([
            'empresa',
            'inscritos.agendaInterlab.interlab',
        ]);

        $query = $this->applySearchFilter($query);
        $query = $this->applyPepFilter($query);
        $query = $this->applyEmpresaFilter($query);
        $query = $this->applySorting($query);

        return $query;
    }

    protected function applySearchFilter($query)
    {
        $search = trim($this->search);

        return $query->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhereHas('empresa', function ($subQuery) use ($search) {
                        $subQuery->where('nome_razao', 'like', "%{$search}%")
                            ->orWhere('cpf_cnpj', 'like', "%{$search}%");
                    });
            });
        });
    }

    protected function applyPepFilter($query)
    {
        $searchPep = trim($this->searchPep);

        return $query->when($searchPep, function ($query) use ($searchPep) {
            $query->whereHas('inscritos.agendaInterlab.interlab', function ($subQuery) use ($searchPep) {
                $subQuery->where('nome', 'like', "%{$searchPep}%");
            });
        });
    }

    protected function applyEmpresaFilter($query)
    {
        return $query->when($this->empresaSelecionada, function ($query) {
            $query->where('empresa_id', $this->empresaSelecionada);
        });
    }

    protected function applySorting($query)
    {
        return $query->when($this->sortBy, function ($query) {
            if ($this->sortBy === 'empresa') {
                $query->orderBy(
                    Pessoa::select('nome_razao')
                        ->whereColumn('pessoas.id', 'interlab_laboratorios.empresa_id'),
                    $this->sortDirection
                );
            } else {
                $query->orderBy($this->sortBy, $this->sortDirection);
            }
        });
    }

    public function render()
    {
        $this->sortBy = in_array($this->sortBy, ['created_at', 'empresa', 'nome'], true) ? $this->sortBy : 'created_at';
        $this->sortDirection = in_array($this->sortDirection, ['ASC', 'DESC'], true) ? $this->sortDirection : 'DESC';
        $this->perPage = max(1, min(100, (int) $this->perPage));

        $laboratorios = $this->getQuery()->paginate($this->perPage);

        return view('livewire.interlab.lab-table', [
            'laboratorios' => $laboratorios,
            'allEmpresas' => $this->getAllEmpresasProperty(),
        ]);
    }
}
