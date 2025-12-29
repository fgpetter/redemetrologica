<?php

namespace App\Livewire\Interlab;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\InterlabLaboratorio;
use App\Models\Pessoa;
use App\Models\Endereco;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class LabTable extends Component
{
    use WithPagination;

    #[Url(as: 'p', history: false)]
    public $perPage = 15;

    #[Url(as: 's', history: false)]
    public $search = '';

    #[Url(as: 'empresa', history: false)]
    public $empresaSelecionada = '';

    #[Url(as: 'sb', history: false)]
    public $sortBy = 'created_at';

    #[Url(as: 'sd', history: false)]
    public $sortDirection = 'DESC';

    // Modal 
    public $showModal = false;
    public $isEdit = false;

    // Lab
    public $labId;
    public $nome;
    public $empresa_id;

    // Endereco
    public $cep;
    public $endereco;
    public $complemento;
    public $bairro;
    public $cidade;
    public $uf;

    protected function rules()
    {
        return [
            'empresa_id' => 'required|exists:pessoas,id',
            'nome' => 'required|string|max:255',
            'cep' => 'required|string|max:9',
            'endereco' => 'required|string|max:255',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'uf' => 'required|string|size:2',
        ];
    }


    public function setSortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'ASC';
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'empresaSelecionada']);
        $this->dispatch('reset-empresa-filter');
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['search', 'perPage', 'empresaSelecionada'])) {
            $this->resetPage();
        }
    }

    public function updatedCep($value)
    {
        $cep = preg_replace('/\D/', '', $value);

        if (strlen($cep) === 8) {
            $localEndereco = Endereco::where('cep', $cep)
                ->orderBy('id', 'desc')
                ->first();

            if ($localEndereco) {
                $this->endereco = $localEndereco->endereco;
                $this->bairro = $localEndereco->bairro;
                $this->cidade = $localEndereco->cidade;
                $this->uf = $localEndereco->uf;
            } else {
                $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

                if ($response->successful() && !isset($response->json()['erro'])) {
                    $data = $response->json();
                    $this->endereco = $data['logradouro'] ?? '';
                    $this->bairro = $data['bairro'] ?? '';
                    $this->cidade = $data['localidade'] ?? '';
                    $this->uf = $data['uf'] ?? '';
                } else {
                    $this->addError('cep', 'CEP não encontrado.');
                }
            }
        } elseif (strlen($cep) > 0) {
            $this->addError('cep', 'CEP inválido. Certifique-se de que possui 8 dígitos.');
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
        $this->resetForm();
        $this->isEdit = true;
        
        $lab = InterlabLaboratorio::where('uid', $uid)->with('endereco')->firstOrFail();
        
        $this->labId = $lab->id;
        $this->empresa_id = $lab->empresa_id;
        $this->nome = $lab->nome;

        if ($lab->endereco) {
            $this->cep = $lab->endereco->cep;
            $this->endereco = $lab->endereco->endereco;
            $this->complemento = $lab->endereco->complemento;
            $this->bairro = $lab->endereco->bairro;
            $this->cidade = $lab->endereco->cidade;
            $this->uf = $lab->endereco->uf;
        }

        $this->dispatch('open-modal');
        $this->dispatch('set-empresa-modal', $this->empresa_id);
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $enderecoData = [
                'pessoa_id' => $this->empresa_id,
                'cep' => $this->cep,
                'endereco' => $this->endereco,
                'complemento' => $this->complemento,
                'bairro' => $this->bairro,
                'cidade' => $this->cidade,
                'uf' => $this->uf,
            ];

            if ($this->isEdit && $this->labId) {
                $lab = InterlabLaboratorio::findOrFail($this->labId);
                if ($lab->endereco_id) {
                    $endereco = Endereco::find($lab->endereco_id);
                    if ($endereco) {
                        $endereco->update($enderecoData);
                    } else {
                        $endereco = Endereco::create($enderecoData);
                        $lab->endereco_id = $endereco->id;
                    }
                } else {
                    $endereco = Endereco::create($enderecoData);
                    $lab->endereco_id = $endereco->id;
                }
            } else {
                $endereco = Endereco::create($enderecoData);
                $lab = new InterlabLaboratorio();
                $lab->endereco_id = $endereco->id;
            }

            $lab->empresa_id = $this->empresa_id;
            $lab->nome = $this->nome;
            $lab->save();
        });

        $this->dispatch('close-modal');
        $this->dispatch('notify', 
            type: 'success', 
            content: $this->isEdit ? 'Laboratório atualizado com sucesso!' : 'Laboratório criado com sucesso!'
        );
        
        $this->resetForm();
    }

    public function delete($uid)
    {
        $lab = InterlabLaboratorio::where('uid', $uid)->firstOrFail();
        DB::transaction(function() use ($lab) {
             $enderecoId = $lab->endereco_id;
             $lab->delete();
             if ($enderecoId) {
                 Endereco::where('id', $enderecoId)->delete();
             }
        });

        $this->dispatch('notify', type: 'success', content: 'Laboratório removido com sucesso!');
    }

    public function resetForm()
    {
        $this->reset([
            'labId', 'nome', 'empresa_id',
            'cep', 'endereco', 'complemento', 'bairro', 'cidade', 'uf',
            'isEdit'
        ]);
        $this->dispatch('reset-empresa-modal');
    }

    public function getEmpresasProperty()
    {
        return Pessoa::query()
            ->select(['id', 'cpf_cnpj', 'nome_razao'])
            ->where('tipo_pessoa', 'PJ')
            ->whereIn('id', InterlabLaboratorio::query()
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
        $query = InterlabLaboratorio::with(['empresa', 'endereco']);

        $query = $this->applySearchFilter($query);
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
        $laboratorios = $this->getQuery()->paginate($this->perPage);

        return view('livewire.interlab.lab-table', [
            'laboratorios' => $laboratorios,
            'allEmpresas' => $this->getAllEmpresasProperty()
        ]);
    }
}
