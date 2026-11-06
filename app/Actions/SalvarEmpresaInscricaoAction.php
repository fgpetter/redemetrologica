<?php

namespace App\Actions;

use App\Enums\EsferaGovernamental;
use App\Enums\FormaPagamentoCobranca;
use App\Models\Pessoa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalvarEmpresaInscricaoAction
{
    /**
     * @param  int|null  $empresaId  Empresa previamente carregada no fluxo de inscrição
     * @param  array<string, mixed>  $empresa  Campos do cadastro e do endereço de cobrança
     * @param  array<string, mixed>  $dadosCobranca  Respostas de cobrança
     * @param  bool  $cepCompleto  Exige a máscara de CEP do formulário de PEP
     * @return Pessoa Empresa e endereço persistidos
     */
    public function execute(?int $empresaId, array $empresa, array $dadosCobranca, bool $cepCompleto = false): Pessoa
    {
        $dados = Validator::make([
            'empresa' => $empresa,
            'dadosCobranca' => $dadosCobranca,
        ], [
            'empresa.nome_razao' => ['required', 'string', 'max:191'],
            'empresa.cpf_cnpj' => ['required', 'cpf_ou_cnpj', 'max:191'],
            'empresa.telefone' => ['nullable', 'celular_com_ddd'],
            'empresa.endereco_cobranca.email' => ['required', 'email', 'max:191'],
            'empresa.endereco_cobranca.cep' => ['required', 'string', ...($cepCompleto ? ['min:9'] : [])],
            'empresa.endereco_cobranca.complemento' => ['nullable', 'string'],
            'empresa.endereco_cobranca.endereco' => ['required', 'string'],
            'empresa.endereco_cobranca.bairro' => ['required', 'string'],
            'empresa.endereco_cobranca.cidade' => ['required', 'string'],
            'empresa.endereco_cobranca.uf' => ['required', 'string', 'size:2'],
            'dadosCobranca.forma_pagamento' => ['required', Rule::enum(FormaPagamentoCobranca::class)],
            'dadosCobranca.exige_pedido_compra' => ['required', 'in:0,1'],
            'dadosCobranca.entidade_governamental' => ['required', 'in:0,1'],
            'dadosCobranca.esfera_governamental' => ['required_if:dadosCobranca.entidade_governamental,1', 'nullable', Rule::enum(EsferaGovernamental::class)],
        ], [
            'empresa.nome_razao.required' => 'Preencha o campo nome/razão social.',
            'empresa.nome_razao.max' => 'O campo nome/razão social deve ter no máximo :max caracteres.',
            'empresa.cpf_cnpj.required' => 'Preencha o campo CPF/CNPJ.',
            'empresa.telefone.celular_com_ddd' => 'Informe um telefone válido com DDD.',
            'empresa.endereco_cobranca.cep.required' => 'Preencha o campo CEP de cobrança.',
            'empresa.endereco_cobranca.cep.min' => 'O campo CEP de cobrança deve ter no mínimo :min caracteres.',
            'empresa.endereco_cobranca.endereco.required' => 'Preencha o campo endereço de cobrança.',
            'empresa.endereco_cobranca.bairro.required' => 'Preencha o campo bairro de cobrança.',
            'empresa.endereco_cobranca.email.required' => 'O email de cobrança é obrigatório.',
            'empresa.endereco_cobranca.email.email' => 'O email de cobrança deve ser um endereço de email válido.',
            'empresa.endereco_cobranca.cidade.required' => 'Preencha o campo cidade de cobrança.',
            'empresa.endereco_cobranca.uf.required' => 'Preencha o campo UF de cobrança.',
            'empresa.endereco_cobranca.uf.size' => 'O campo UF de cobrança deve ter exatamente 2 caracteres.',
            'dadosCobranca.forma_pagamento.required' => 'Selecione a forma de pagamento.',
            'dadosCobranca.exige_pedido_compra.required' => 'Informe se é necessário envio de pedido/ordem de compra ou empenho.',
            'dadosCobranca.entidade_governamental.required' => 'Informe se é entidade governamental.',
            'dadosCobranca.esfera_governamental.required_if' => 'Selecione a esfera governamental.',
        ])->validate();

        return DB::transaction(function () use ($empresaId, $dados): Pessoa {
            $dadosEmpresa = $dados['empresa'];
            $cnpj = preg_replace('/[^0-9]/', '', $dadosEmpresa['cpf_cnpj']);

            if ($empresaId !== null) {
                $empresa = Pessoa::query()->where('tipo_pessoa', 'PJ')->lockForUpdate()->findOrFail($empresaId);
                if (preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj) !== $cnpj) {
                    throw ValidationException::withMessages(['empresa.cpf_cnpj' => 'O documento deve ser o mesmo da empresa selecionada.']);
                }
            } else {
                if (Pessoa::withTrashed()->where('cpf_cnpj', $cnpj)->exists()) {
                    throw ValidationException::withMessages(['empresa.cpf_cnpj' => 'Este documento já está cadastrado. Busque a empresa pelo CNPJ.']);
                }

                $empresa = new Pessoa(['cpf_cnpj' => $cnpj, 'tipo_pessoa' => 'PJ']);
            }

            $empresa->fill([
                'nome_razao' => $dadosEmpresa['nome_razao'],
                'telefone' => $dadosEmpresa['telefone'] ?? null,
            ])->save();

            $dadosEndereco = $dadosEmpresa['endereco_cobranca'];
            $endereco = $empresa->enderecos()->whereKey($empresa->end_cobranca)->first();
            $camposEndereco = [
                'info' => 'Cobrança',
                'cep' => $dadosEndereco['cep'],
                'endereco' => $dadosEndereco['endereco'],
                'complemento' => $dadosEndereco['complemento'] ?? null,
                'bairro' => $dadosEndereco['bairro'],
                'cidade' => $dadosEndereco['cidade'],
                'uf' => $dadosEndereco['uf'],
                'email' => $dadosEndereco['email'],
                'cobranca' => 1,
            ];

            if ($endereco) {
                $endereco->update($camposEndereco);
            } else {
                $endereco = $empresa->enderecos()->create($camposEndereco);
            }

            $empresa->update([
                'end_cobranca' => $endereco->id,
                'email_cobranca' => $endereco->email,
            ]);

            return $empresa->setRelation('enderecoCobranca', $endereco);
        });
    }
}
