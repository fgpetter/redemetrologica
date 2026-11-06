<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CriarLancamentoFinanceiroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_emissao' => ['nullable', Rule::date()->format('Y-m-d')],
            'nota_fiscal' => ['nullable', 'string', 'max:191'],
            'consiliacao' => ['nullable', 'string', 'max:191'],
            'documento' => ['nullable', 'string', 'max:191'],
            'pessoa_id' => ['required', 'exists:pessoas,id'],
            'centro_custo_id' => ['required', 'exists:centro_custos,id'],
            'plano_conta_id' => ['required', 'exists:plano_contas,id'],
            'historico' => ['nullable', 'string', 'max:900'],
            'tipo_lancamento' => ['required', 'in:CREDITO,DEBITO'],
            'valor' => ['nullable', 'string', 'max:11'],
            'data_vencimento' => ['nullable', Rule::date()->format('Y-m-d')],
            'data_pagamento' => ['nullable', Rule::date()->format('Y-m-d')],
            'modalidade_pagamento_id' => ['nullable', 'exists:modalidade_pagamentos,id'],
            'observacoes' => ['nullable', 'string'],
            'duplicar_lancamento' => ['nullable', 'boolean'],
            'lancamento_original_uid' => ['nullable', 'string', Rule::exists('lancamentos_financeiros', 'uid')],
        ];
    }

    public function messages(): array
    {
        return [
            'data_emissao.date' => 'A data de emissão deve ser uma data válida',
            'data_emissao.date_format' => 'A data de emissão deve ser uma data válida',
            'nota_fiscal.string' => 'O número da nota fiscal é inválido',
            'nota_fiscal.max' => 'Máximo de caracteres excedido',
            'consiliacao.string' => 'O número da consiliação é inválido',
            'consiliacao.max' => 'Máximo de caracteres excedido',
            'documento.string' => 'O número do documento é inválido',
            'documento.max' => 'Máximo de caracteres excedido',
            'pessoa_id.required' => 'É necessário selecionar uma pessoa',
            'pessoa_id.exists' => 'A pessoa selecionada não existe no sistema',
            'centro_custo_id.required' => 'É necessário selecionar um centro de custo',
            'centro_custo_id.exists' => 'O centro de custo selecionado não existe no sistema',
            'plano_conta_id.required' => 'É necessário selecionar um plano de conta',
            'plano_conta_id.exists' => 'O plano de conta selecionado não existe no sistema',
            'historico.string' => 'O histórico informado é inválido',
            'historico.max' => 'Máximo de caracteres excedido',
            'tipo_lancamento.required' => 'É necessário selecionar o tipo de lançamento',
            'tipo_lancamento.in' => 'O tipo de lançamento selecionado é inválido',
            'valor.numeric' => 'O valor deve ser um número válido',
            'data_vencimento.date' => 'A data de vencimento deve ser uma data válida',
            'data_vencimento.date_format' => 'A data de vencimento deve ser uma data válida',
            'data_pagamento.date' => 'A data de pagamento deve ser uma data válida',
            'data_pagamento.date_format' => 'A data de pagamento deve ser uma data válida',
            'modalidade_pagamento_id.exists' => 'A modalidade de pagamento selecionada não existe no sistema',
            'observacoes.string' => 'As observações informadas são inválidas',
            'observacoes.max' => 'Máximo de caracteres excedido',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user(),
            'request' => $this->all(),
            'uri' => $this->fullUrl(),
            'method' => 'App\\Http\\Controllers\\LancamentoFinanceiroController::store',
            'errors' => $validator->errors(),
        ]);
        $redirect = back()->with('error', 'Houve um erro a processar os dados, tente novamente')->withErrors($validator)->withInput();

        if ($this->boolean('duplicar_lancamento')) {
            $redirect->with('duplicar_lancamento_uid', $this->input('lancamento_original_uid'));
        }

        throw new HttpResponseException($redirect);
    }
}
