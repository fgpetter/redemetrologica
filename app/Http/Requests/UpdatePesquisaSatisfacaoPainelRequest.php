<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class UpdatePesquisaSatisfacaoPainelRequest extends FormRequest
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
            'nota_1' => ['nullable', 'integer', 'min:0', 'max:10'],
            'nota_2' => ['nullable', 'integer', 'min:0', 'max:10'],
            'nota_3' => ['nullable', 'integer', 'min:0', 'max:10'],
            'nota_4' => ['nullable', 'integer', 'min:0', 'max:10'],
            'nota_5' => ['nullable', 'integer', 'min:0', 'max:10'],
            'nota_6' => ['nullable', 'integer', 'min:0', 'max:10'],
            'nota_7' => ['nullable', 'integer', 'min:0', 'max:10'],
            'criterios_harmoniosos' => ['nullable', 'string'],
            'divergencias' => ['nullable', 'string'],
            'pontos_melhoria' => ['nullable', 'string'],
            'comentarios' => ['nullable', 'array'],
            'comentarios.*' => ['nullable', 'string'],
            'responsavel' => ['nullable', 'string', 'max:191'],
            'conferida' => ['nullable', 'numeric', 'in:0,1'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user()->id ?? null,
            'errors' => $validator->errors(),
            'request' => $this->all(),
        ]);

        throw new HttpResponseException(
            back()->withErrors($validator)->withInput()->with('error', 'Revise os dados informados.')
        );
    }
}
