<?php

namespace App\Livewire\PainelCliente;

use App\Actions\BuscaCepAction;
use App\Models\Pessoa;
use App\Models\InterlabInscrito;
use Livewire\Component;
use Livewire\Attributes\On;

class ConfirmaCNPJ extends Component
{
    public $empresa = [];
    public $isOpen = false;
    public $isVisible = false; 

    public function mount()
    {
        $this->resetEmpresa();
    }

    private function resetEmpresa() {
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
        $empresaModel = Pessoa::with('enderecoCobranca')->find($id_pessoa);
        
        if ($empresaModel) {
            $this->empresa = $empresaModel->toArray();
            
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
                        'bairro' => '', 'cidade' => '', 'uf' => '', 'email' => ''
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
        $this->isOpen = !$this->isOpen;
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

    public function salvar()
    {
        $this->validate([
            'empresa.nome_razao' => ['required', 'string', 'max:191'],
            'empresa.cpf_cnpj' => ['required', 'cpf_ou_cnpj', 'max:191'],
            'empresa.endereco_cobranca.email' => ['required', 'email', 'max:191'],
            'empresa.endereco_cobranca.cep' => ['required', 'string', 'min:9'],
            'empresa.endereco_cobranca.endereco' => ['required', 'string'],
            'empresa.endereco_cobranca.bairro' => ['required', 'string'],
            'empresa.endereco_cobranca.cidade' => ['required', 'string'],
            'empresa.endereco_cobranca.uf' => ['required', 'string', 'size:2'],
        ], [
                'empresa.nome_razao.required' => 'Preencha o campo nome/razão social.',
                'empresa.nome_razao.max' => 'O campo nome/razão social deve ter no máximo :max caracteres.',
                'empresa.cpf_cnpj.required' => 'Preencha o campo CPF/CNPJ.',
                'empresa.endereco_cobranca.cep.required' => 'Preencha o campo CEP de cobrança.',
                'empresa.endereco_cobranca.cep.min' => 'O campo CEP de cobrança deve ter no mínimo :min caracteres.',
                'empresa.endereco_cobranca.endereco.required' => 'Preencha o campo endereço de cobrança.',
                'empresa.endereco_cobranca.bairro.required' => 'Preencha o campo bairro de cobrança.',
                'empresa.endereco_cobranca.email.required' => 'O email de cobrança é obrigatório.',
                'empresa.endereco_cobranca.email.email' => 'O email de cobrança deve ser um endereço de email válido.',
                'empresa.endereco_cobranca.cidade.required' => 'Preencha o campo cidade de cobrança.',
                'empresa.endereco_cobranca.uf.required' => 'Preencha o campo UF de cobrança.',
                'empresa.endereco_cobranca.uf.size' => 'O campo UF de cobrança deve ter exatamente 2 caracteres.',
        ]);

        $empresa = Pessoa::updateOrCreate(
             ['id' => $this->empresa['id'] ?? null],
             [
                 'nome_razao' => $this->empresa['nome_razao'],
                 'cpf_cnpj' => $this->empresa['cpf_cnpj'],
                 'telefone' => $this->empresa['telefone'] ?? null,
                 'tipo_pessoa' => 'PJ',
             ]
        );
 
        $enderecoCobranca = $empresa->enderecoCobranca()->updateOrCreate(
             ['pessoa_id' => $empresa->id],
             [
                 'info' => 'Cobrança',
                 'cep' => $this->empresa['endereco_cobranca']['cep'],
                 'endereco' => $this->empresa['endereco_cobranca']['endereco'],
                 'complemento' => $this->empresa['endereco_cobranca']['complemento'] ?? null,
                 'bairro' => $this->empresa['endereco_cobranca']['bairro'],
                 'cidade' => $this->empresa['endereco_cobranca']['cidade'],
                 'uf' => $this->empresa['endereco_cobranca']['uf'],
                 'email' => $this->empresa['endereco_cobranca']['email'],
                 'cobranca' => 1,
             ]
        );
 
        $empresa->update([
             'end_cobranca' => $enderecoCobranca->id,
             'email_cobranca' => $enderecoCobranca->email,
        ]);

        $empresaReloaded = Pessoa::with('enderecoCobranca')->find($empresa->id);
        $this->empresa = $empresaReloaded->toArray();
        
        $this->isOpen = false;

        $this->dispatch('empresaSaved', empresa_id: $empresa->id);
    }

    public function render()
    {
        return view('livewire.painel-cliente.confirma-cnpj');
    }
}
