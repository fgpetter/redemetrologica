<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AtualizarEnderecosAvaliadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPessoalGroupFilled = collect(['pessoal_cep', 'pessoal_endereco', 'pessoal_bairro', 'pessoal_cidade', 'pessoal_uf'])->contains(fn ($field) => $this->filled($field));
        $isComercialGroupFilled = collect(['comercial_cep', 'comercial_endereco', 'comercial_bairro', 'comercial_cidade', 'comercial_uf'])->contains(fn ($field) => $this->filled($field));

        return [
            'pessoal_cep' => ['nullable', 'string', 'max:9', 'min:9', Rule::requiredIf($isPessoalGroupFilled)],
            'pessoal_endereco' => ['nullable', 'string', 'max:255', Rule::requiredIf($isPessoalGroupFilled)],
            'pessoal_complemento' => 'nullable|string|max:255',
            'pessoal_bairro' => ['nullable', 'string', 'max:255', Rule::requiredIf($isPessoalGroupFilled)],
            'pessoal_cidade' => ['nullable', 'string', 'max:255', Rule::requiredIf($isPessoalGroupFilled)],
            'pessoal_uf' => ['nullable', 'string', 'max:2', Rule::requiredIf($isPessoalGroupFilled)],
            'comercial_cep' => ['nullable', 'string', 'max:9', 'min:9', Rule::requiredIf($isComercialGroupFilled)],
            'comercial_endereco' => ['nullable', 'string', 'max:255', Rule::requiredIf($isComercialGroupFilled)],
            'comercial_complemento' => 'nullable|string|max:255',
            'comercial_bairro' => ['nullable', 'string', 'max:255', Rule::requiredIf($isComercialGroupFilled)],
            'comercial_cidade' => ['nullable', 'string', 'max:255', Rule::requiredIf($isComercialGroupFilled)],
            'comercial_uf' => ['nullable', 'string', 'max:2', Rule::requiredIf($isComercialGroupFilled)],
        ];
    }

    public function messages(): array
    {
        return [
            'pessoal_cep.required' => 'O CEP  é obrigatório.',
            'pessoal_endereco.required' => 'O endereço é obrigatório.',
            'pessoal_bairro.required' => 'O bairro é obrigatório.',
            'pessoal_cidade.required' => 'A cidade é obrigatória.',
            'pessoal_uf.required' => 'O UF é obrigatório.',
            'comercial_cep.required' => 'O CEP é obrigatório.',
            'comercial_endereco.required' => 'O endereço comercial é obrigatório.',
            'comercial_bairro.required' => 'O bairro é obrigatório.',
            'comercial_cidade.required' => 'A cidade é obrigatória.',
            'comercial_uf.required' => 'O UF é obrigatório.',
            'pessoal_cep.min' => 'O CEP deve conter exatamente 9 caracteres (incluindo o hífen).',
            'comercial_cep.min' => 'O CEP deve conter exatamente 9 caracteres (incluindo o hífen).',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', ['user' => auth()->user(), 'request' => $this->all(), 'uri' => $this->fullUrl(), 'method' => static::class, 'errors' => $validator->errors()]);
        throw new HttpResponseException(redirect()->back()->withErrors($validator)->withInput()->withFragment('enderecos'));
    }
}
