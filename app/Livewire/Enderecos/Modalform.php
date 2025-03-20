<?php

namespace App\Livewire\Enderecos;

use App\Models\Pessoa;
use Livewire\Component;
use App\Models\Endereco;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ModalForm extends Component
{
    public Pessoa $pessoa;
    public ?string $enderecoUid;
    public array $endereco = [];
    public bool $saved = false;

    protected $rules = [
        'endereco.pessoa_id' => 'required|integer',
        'endereco.info' => 'nullable|string|max:255',
        'endereco.cep' => 'required|string|size:9',
        'endereco.endereco' => 'required|string|max:255',
        'endereco.complemento' => 'nullable|string|max:255',
        'endereco.bairro' => 'nullable|string|max:255',
        'endereco.cidade' => 'required|string|max:255',
        'endereco.uf' => 'required|string|size:2',
    ];

    protected $messages = [
        'endereco.cep.required' => 'Preencha o campo CEP',
        'endereco.cep.size' => 'O CEP precisa ter 9 dígitos',
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
                'end_padrao' => false,
            ];
        }
    }

    public function buscaCep()
    {
        $cep = $this->endereco['cep'];

        if (strlen($cep) === 9) {
            $localEndereco = Endereco::where('cep', $cep)
                ->orderBy('id', 'desc')
                ->first();

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
        $dadosValidados = $this->validate();

        DB::transaction(function () use ($dadosValidados) {
            $endereco = Endereco::updateOrCreate(
                ['uid' => $this->endereco['uid']],
                $dadosValidados['endereco']
            );

            if ($this->endereco['end_padrao']) {
                $this->pessoa->update(['end_padrao' => $endereco->id]);
            } elseif ($this->pessoa->end_padrao === $endereco->id) {
                $this->pessoa->update(['end_padrao' => null]);
            }
        });

        $this->saved = true;
        $this->dispatch('refresh-enderecos-list');
    }

    public function render()
    {
        return view('livewire.enderecos.modalform');
    }
}