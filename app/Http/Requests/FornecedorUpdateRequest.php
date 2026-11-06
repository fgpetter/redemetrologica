<?php

namespace App\Http\Requests;

use App\Enums\FornecedorArea;
use App\Helpers\Telefone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class FornecedorUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $areasData = $this->input('areas_data', []);
        foreach (is_array($areasData) ? $areasData : [] as $area => $data) {
            if (is_array($data) && array_key_exists('pessoa_contato_telefone', $data)) {
                $areasData[$area]['pessoa_contato_telefone'] = Telefone::paraValidacao($data['pessoa_contato_telefone']);
            }
        }
        $this->merge(['areas_data' => $areasData]);

    }

    public function rules(): array
    {
        return [
            'fornecedor_desde' => ['nullable', Rule::date()->format('Y-m-d')],
            'ativo' => ['required', 'in:0,1'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
            'areas' => ['nullable', 'array'],
            'areas.*' => ['in:'.implode(',', array_column(FornecedorArea::cases(), 'value'))],
            'areas_data' => ['nullable', 'array'],
            'areas_data.*.atuacao' => ['nullable', 'string', 'max:191'],
            'areas_data.*.pessoa_contato' => ['nullable', 'string', 'max:191'],
            'areas_data.*.pessoa_contato_email' => ['nullable', 'email', 'max:191'],
            'areas_data.*.pessoa_contato_telefone' => ['nullable', 'celular_com_ddd'],
        ];
    }

    public function messages(): array
    {
        return [
            'fornecedor_desde.date' => 'Data de início inválida',
            'fornecedor_desde.date_format' => 'Data de início inválida',
            'ativo.required' => 'Informe se o fornecedor está ativo',
            'ativo.in' => 'Valor de ativo inválido',
            'observacoes.max' => 'Observações inválidas',
            'areas.*.in' => 'Área inválida',
            'areas_data.*.pessoa_contato_telefone.celular_com_ddd' => 'Informe um telefone válido com DDD.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user() ?? null,
            'request' => $this->all() ?? null,
            'uri' => $this->fullUrl(),
            'method' => $this->route()->getActionName(),
            'errors' => $validator->errors(),
        ]);
        throw new HttpResponseException(back()->withErrors($validator, 'principal')->withInput()->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente'));
    }
}
