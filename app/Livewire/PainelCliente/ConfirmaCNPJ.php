<?php

namespace App\Livewire\PainelCliente;

use App\Actions\BuscaCepAction;
use App\Actions\SalvarEmpresaInscricaoAction;
use App\Helpers\Telefone;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class ConfirmaCNPJ extends Component
{
    #[Locked]
    public ?int $empresaId = null;

    public $empresa = [];

    public array $dadosCobranca = [
        'forma_pagamento' => null,
        'exige_pedido_compra' => null,
        'entidade_governamental' => null,
        'esfera_governamental' => null,
    ];

    public $isOpen = false;

    public $isVisible = false;

    public function mount()
    {
        $this->resetEmpresa();
    }

    private function resetEmpresa()
    {
        $this->empresaId = null;
        $this->empresa = [
            'id' => null,
            'nome_razao' => '',
            'cpf_cnpj' => '',
            'telefone' => '',
            'email' => '',
            'endereco_cobranca' => [
                'cep' => '',
                'endereco' => '',
                'complemento' => '',
                'bairro' => '',
                'cidade' => '',
                'uf' => '',
                'email' => '',
            ],
        ];
    }

    #[On('cnpjFound')]
    public function loadEmpresa($id_pessoa)
    {
        $empresaModel = Pessoa::with('enderecoCobranca')->where('tipo_pessoa', 'PJ')->find($id_pessoa);

        if ($empresaModel) {
            $this->empresaId = $empresaModel->id;
            $this->empresa = $empresaModel->toArray();
            $this->empresa['telefone'] = $empresaModel->getRawOriginal('telefone');

            if (empty($this->empresa['endereco_cobranca'])) {
                $empresa_end = Pessoa::with('enderecos')
                    ->where('id', $id_pessoa)
                    ->first();

                if ($empresa_end && $empresa_end->enderecos->count() === 1) {
                    $endereco = $empresa_end->enderecos->first();
                    $this->empresa['endereco_cobranca'] = [
                        'cep' => $endereco['cep'],
                        'endereco' => $endereco['endereco'],
                        'complemento' => $endereco['complemento'] ?? '',
                        'bairro' => $endereco['bairro'],
                        'cidade' => $endereco['cidade'],
                        'uf' => $endereco['uf'],
                        'email' => $this->empresa['email'] ?? '',
                    ];
                } else {
                    $this->empresa['endereco_cobranca'] = [
                        'cep' => '', 'endereco' => '', 'complemento' => '',
                        'bairro' => '', 'cidade' => '', 'uf' => '', 'email' => '',
                    ];
                }
            }
        }

        $this->isVisible = true;
        $this->isOpen = true;
    }

    #[On('cnpjNotFound')]
    public function newEmpresa($cnpj)
    {
        $this->resetEmpresa();
        $this->empresa['cpf_cnpj'] = $cnpj;
        $this->isVisible = true;
        $this->isOpen = true;
    }

    public function toggleAccordion()
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function buscaCep(BuscaCepAction $buscaCepAction)
    {
        $cep = $this->empresa['endereco_cobranca']['cep'] ?? '';

        $dados = $buscaCepAction->execute($cep);

        if ($dados) {
            $this->empresa['endereco_cobranca']['endereco'] = $dados['endereco'];
            $this->empresa['endereco_cobranca']['bairro'] = $dados['bairro'];
            $this->empresa['endereco_cobranca']['cidade'] = $dados['cidade'];
            $this->empresa['endereco_cobranca']['uf'] = $dados['uf'];
        } else {
            $this->addError('empresa.endereco_cobranca.cep', 'CEP não encontrado.');
        }
    }

    public function salvar(SalvarEmpresaInscricaoAction $salvarEmpresa): void
    {
        $this->empresa['telefone'] = Telefone::paraValidacao($this->empresa['telefone'] ?? null);
        $empresa = $salvarEmpresa->execute($this->empresaId, $this->empresa, $this->dadosCobranca, cepCompleto: true);
        $this->empresaId = $empresa->id;
        $this->empresa = $empresa->toArray();
        $this->empresa['telefone'] = $empresa->getRawOriginal('telefone');

        $this->isOpen = false;

        $this->dispatch(
            'empresaSaved',
            empresa_id: $empresa->id,
            dados_cobranca: LancamentoFinanceiro::dadosCobrancaDoFormulario($this->dadosCobranca),
        );
    }

    public function render()
    {
        return view('livewire.painel-cliente.confirma-cnpj');
    }
}
