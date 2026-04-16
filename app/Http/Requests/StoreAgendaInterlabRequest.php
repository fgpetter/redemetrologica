<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreAgendaInterlabRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'interlab_id' => ['required', 'numeric', 'exists:interlabs,id'],
            'status' => ['required', 'string', 'in:AGENDADO,CONFIRMADO,CONCLUIDO'],
            'inscricao' => ['nullable', 'numeric'],
            'site' => ['nullable', 'numeric'],
            'destaque' => ['nullable', 'numeric'],
            'descricao' => ['nullable', 'string'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['nullable', 'date'],
            'instrucoes_inscricao' => ['nullable', 'string'],
            'ano_referencia' => ['nullable', 'integer'],
            'data_limite_inscricao' => ['required', 'date'],
            'valor_desconto' => ['nullable', 'string'],
            'valores' => ['nullable', 'array'],
            'valores.*.descricao' => ['nullable', 'string'],
            'valores.*.valor' => ['nullable', 'string'],
            'valores.*.valor_assoc' => ['nullable', 'string'],
            'valores.*.analistas' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'interlab_id.required' => 'Selecione um interlab',
            'interlab_id.exists' => 'Opção inválida',
            'interlab_id.numeric' => 'Opção inválida',
            'status.required' => 'O campo status obrigatório',
            'status.in' => 'Opção inválida',
            'inscricao.numeric' => 'Opção inválida',
            'site.numeric' => 'Opção inválida',
            'destaque.numeric' => 'Opção inválida',
            'descricao.string' => 'Permitido somente texto',
            'data_inicio.required' => 'O campo data obrigatório',
            'data_inicio.date' => 'Permitido somente data',
            'data_fim.date' => 'Permitido somente data',
            'instrucoes_inscricao.string' => 'Permitido somente texto',
            'ano_referencia.integer' => 'Ano referência inválido',
            'data_limite_inscricao.date' => 'Data inválida',
            'valor_desconto.string' => 'Valor com desconto inválido',
            'valores.array' => 'Valores inválidos',
            'valores.*.descricao.string' => 'Descrição inválida',
            'valores.*.valor.string' => 'Valor inválido',
            'valores.*.valor_assoc.string' => 'Valor associado inválido',
            'valores.*.analistas.integer' => 'Analistas inválidos',
        ];
    }
    /**
     * Handle a failed validation attempt.
     */
    public function failedValidation(Validator $validator)
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user() ?? null,
            'request' => $this->all() ?? null,
            'uri' => request()->fullUrl() ?? null,
            'method' => get_class($this) . '::' . __FUNCTION__,
            'errors' => $validator->errors() ?? null,
        ]);

        return back()
            ->withErrors($validator, 'principal')
            ->withInput()
            ->with('error', 'Ocorreu um erro, revise os dados salvos e tente novamente');
    }
}
