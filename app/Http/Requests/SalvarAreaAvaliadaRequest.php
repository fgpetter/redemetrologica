<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SalvarAreaAvaliadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'avaliacao_id' => ['required', 'exists:agenda_avaliacoes,id'],
            'area_atuacao_id' => ['required', 'exists:areas_atuacao,id'],
            'situacao' => ['nullable', 'string', Rule::in(['ATIVO', 'AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LIDER', 'ESPECIALISTA', 'INATIVO'])],
            'data_inicial' => ['nullable', Rule::date()->format('Y-m-d')],
            'data_final' => ['nullable', Rule::date()->format('Y-m-d')],
            'avaliador_id' => ['required', 'exists:avaliadores,id'],
            'num_ensaios' => ['nullable', 'integer'],
            'dias' => ['nullable', 'numeric', 'min:0.5'],
            'valor_dia' => ['nullable'],
            'valor_lider' => ['nullable'],
            'valor_estim_desloc' => ['nullable'],
            'valor_estim_alim' => ['nullable'],
            'valor_estim_hosped' => ['nullable'],
            'valor_estim_extras' => ['nullable'],
            'valor_real_desloc' => ['nullable'],
            'valor_real_alim' => ['nullable'],
            'valor_real_hosped' => ['nullable'],
            'valor_real_extras' => ['nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avaliacao_id.required' => 'Dados inválidos, selecione uma avaliação e envie novamente',
            'avaliacao_id.exists' => 'Dados inválidos, selecione uma avaliação e envie novamente',
            'area_atuacao_id.required' => 'Dados inválidos, selecione uma area e envie novamente',
            'area_atuacao_id.exists' => 'Dados inválidos, selecione uma area e envie novamente',
            'situacao.in' => 'Selecione uma opção válida',
            'data_inicial.date' => 'Data inicial inválida',
            'data_inicial.date_format' => 'Data inicial inválida',
            'data_final.date' => 'Data final inválida',
            'data_final.date_format' => 'Data final inválida',
            'avaliador_id.required' => 'Selecione um avaliador e envie novamente',
            'avaliador_id.exists' => 'Selecione um avaliador e envie novamente',
            'num_ensaios.integer' => 'O dado enviado não é valido',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user()->id ?? null,
            'errors' => $validator->errors(),
            'request' => $this->all(),
        ]);

        return back()->withErrors($validator)->withInput()->with('error', 'Revise os dados informados.');
    }
}
