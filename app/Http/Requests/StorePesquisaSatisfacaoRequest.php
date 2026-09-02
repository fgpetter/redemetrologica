<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StorePesquisaSatisfacaoRequest extends FormRequest
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
        $rules = [
            'nota_1' => ['required', 'integer', 'min:0', 'max:10'],
            'nota_2' => ['required', 'integer', 'min:0', 'max:10'],
            'nota_3' => ['required', 'integer', 'min:0', 'max:10'],
            'nota_4' => ['required', 'integer', 'min:0', 'max:10'],
            'nota_5' => ['required', 'integer', 'min:0', 'max:10'],
            'nota_6' => ['required', 'integer', 'min:0', 'max:10'],
            'nota_7' => ['required', 'integer', 'min:0', 'max:10'],
            'criterios_harmoniosos' => ['required', 'string'],
            'divergencias' => ['required', 'string'],
            'pontos_melhoria' => ['required', 'string'],
            'comentarios' => ['required', 'array'],
            'responsavel' => ['required', 'string', 'max:191'],
        ];

        $avaliacao = $this->route('avaliacao');
        $ids = $avaliacao?->areas()->pluck('avaliador_id')->unique() ?? collect();

        foreach ($ids as $id) {
            $rules["comentarios.{$id}"] = ['required', 'string'];
        }

        return $rules;
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
