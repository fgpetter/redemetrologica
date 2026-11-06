<?php

namespace App\Livewire\Enderecos;

use App\Livewire\Forms\EnderecoForm;
use App\Models\Endereco;
use App\Models\Pessoa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ModalForm extends Component
{
    public Pessoa $pessoa;

    #[Locked]
    public ?string $enderecoUid;

    public EnderecoForm $endereco;

    public bool $saved = false;

    public function mount()
    {
        if ($this->enderecoUid) {
            $endereco = $this->pessoa->enderecos()->where('uid', $this->enderecoUid)->firstOrFail();
            $this->endereco->fill($endereco->only(['info', 'cep', 'endereco', 'complemento', 'bairro', 'cidade', 'uf']));
            $this->endereco->end_padrao = $this->pessoa->end_padrao === $endereco->id;
        }
    }

    public function buscaCep()
    {
        $cep = $this->endereco->cep;

        if (strlen($cep) === 9) {
            $localEndereco = Endereco::where('cep', $cep)
                ->orderBy('id', 'desc')
                ->first();

            if ($localEndereco) {
                $this->endereco->endereco = $localEndereco->endereco;
                $this->endereco->bairro = $localEndereco->bairro;
                $this->endereco->cidade = $localEndereco->cidade;
                $this->endereco->uf = $localEndereco->uf;
            } else {
                $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

                if ($response->successful() && ! isset($response->json()['erro'])) {
                    $data = $response->json();
                    $this->endereco->endereco = $data['logradouro'] ?? '';
                    $this->endereco->bairro = $data['bairro'] ?? '';
                    $this->endereco->cidade = $data['localidade'] ?? '';
                    $this->endereco->uf = $data['uf'] ?? '';
                }
            }
        }
    }

    public function salvar()
    {
        abort_unless(auth()->user()?->hasPermissionTo(['funcionario', 'admin']), 404);

        $dadosValidados = $this->endereco->validate();

        DB::transaction(function () use ($dadosValidados) {
            $padrao = $dadosValidados['end_padrao'];
            unset($dadosValidados['end_padrao']);

            if ($this->enderecoUid) {
                $endereco = $this->pessoa->enderecos()->where('uid', $this->enderecoUid)->firstOrFail();
                $endereco->update($dadosValidados);
            } else {
                $endereco = $this->pessoa->enderecos()->create($dadosValidados);
                $this->enderecoUid = $endereco->uid;
            }

            if ($padrao) {
                $this->pessoa->update(['end_padrao' => $endereco->id]);
            } elseif ($this->pessoa->end_padrao === $endereco->id) {
                $this->pessoa->update(['end_padrao' => null]);
            }
        });

        $this->saved = true;
        $this->dispatch('refresh-enderecos-'.$this->pessoa->id);
        $this->dispatch('refresh-enderecos-list');
    }

    public function render()
    {
        return view('livewire.enderecos.modal-form');
    }
}
