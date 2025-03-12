<?php

namespace App\Livewire\Enderecos;

use Livewire\Component;
use App\Models\Endereco;
use App\Models\Pessoa;
use Illuminate\Support\Facades\Http;

class ModalForm extends Component
{
    public Pessoa $pessoa;
    public ?string $enderecoUid;
    public array $endereco = [];
    
    protected $rules = [
        'endereco.pessoa_id' => 'required|integer',
        'endereco.info' => 'nullable|string|max:255',
        'endereco.cep' => 'required|string|max:9',
        'endereco.endereco' => 'required|string|max:255',
        'endereco.complemento' => 'nullable|string|max:255',
        'endereco.bairro' => 'nullable|string|max:255',
        'endereco.cidade' => 'required|string|max:255',
        'endereco.uf' => 'required|string|size:2',
        'endereco.end_padrao' => 'nullable|boolean',
    ];

    protected $messages = [
        'endereco.cep.required' => 'Preencha o campo CEP',
        'endereco.endereco.required' => 'Preencha o campo endereço',
        'endereco.cidade.required' => 'Preencha o campo cidade',
        'endereco.uf.required' => 'Preencha o campo UF',
        'endereco.uf.size' => 'UF deve ter 2 caracteres',
    ];

    public function mount()
    {
        if ($this->enderecoUid) {
            $endereco = Endereco::where('uid', $this->enderecoUid)->first();
            $this->endereco = $endereco->toArray();
            $this->endereco['end_padrao'] = $this->pessoa->end_padrao === $endereco->id;
        } else {
            $this->endereco = [
                'uid' => uniqid(),
                'pessoa_id' => $this->pessoa->id,
                'info' => null,
                'cep' => null,
                'endereco' => null,
                'complemento' => null,
                'bairro' => null,
                'cidade' => null,
                'uf' => null,
                'end_padrao' => false,
            ];
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function buscaCep()
    {
        $cep = preg_replace('/\D/', '', $this->endereco['cep']);
        
        if (strlen($cep) === 8) {
            $localEndereco = Endereco::where('cep', $cep)->first();
            
            if ($localEndereco) {
                $this->endereco['endereco'] = $localEndereco->endereco;
                $this->endereco['bairro'] = $localEndereco->bairro;
                $this->endereco['cidade'] = $localEndereco->cidade;
                $this->endereco['uf'] = $localEndereco->uf;
            } else {
                $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");
                
                if ($response->successful() && !isset($response->json()['erro'])) {
                    $data = $response->json();
                    $this->endereco['endereco'] = $data['logradouro'] ?? '';
                    $this->endereco['bairro'] = $data['bairro'] ?? '';
                    $this->endereco['cidade'] = $data['localidade'] ?? '';
                    $this->endereco['uf'] = $data['uf'] ?? '';
                }
            }
        }
    }

    public function salvar()
    {
        $this->validate();

        $endereco = Endereco::updateOrCreate(
            ['uid' => $this->endereco['uid']],
            $this->endereco
        );

        $this->handleEnderecoPadrao($endereco);

        return redirect()->to(url()->previous())->with('success', 'Endereço salvo com sucesso');
    }

    protected function handleEnderecoPadrao(Endereco $endereco)
    {
        if ($this->endereco['end_padrao']) {
            $this->pessoa->update(['end_padrao' => $endereco->id]);
        } elseif ($this->pessoa->end_padrao === $endereco->id) {
            $this->pessoa->update(['end_padrao' => null]);
        }
    }

    public function render()
    {
        return view('livewire.enderecos.modal-form');
    }
}