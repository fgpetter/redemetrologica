<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class AtualizarCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'descricao' => ['nullable', 'string', 'max:190'],
            'tipo_curso' => ['nullable', 'string', 'in:OFICIAL,SUPLENTE,OUTROS'],
            'carga_horaria' => ['nullable', 'numeric'],
            'objetivo' => ['nullable', 'string'],
            'publico_alvo' => ['nullable', 'string'],
            'pre_requisitos' => ['nullable', 'string'],
            'exemplos_praticos' => ['nullable', 'string'],
            'referencias_utilizadas' => ['nullable', 'string'],
            'conteudo_programatico' => ['nullable', 'string'],
            'observacoes_internas' => ['nullable', 'string'],
            'folder' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'pdf'])->max(2 * 1024)],
            'thumb' => ['nullable', File::types(['jpg', 'jpeg', 'png'])->max(2 * 1024)],
        ];
    }

    public function messages(): array
    {
        return [
            'descricao.string' => 'O campo aceita somente texto.',
            'tipo_curso.in' => 'A opção selecionada é inválida',
            'objetivo.string' => 'O campo aceita somente texto.',
            'publico_alvo.string' => 'O campo aceita somente texto.',
            'pre_requisitos.string' => 'O campo aceita somente texto.',
            'exemplos_praticos.string' => 'O campo aceita somente texto.',
            'referencias_utilizadas.string' => 'O campo aceita somente texto.',
            'conteudo_programatico.string' => 'O campo aceita somente texto.',
            'observacoes_internas.string' => 'O campo aceita somente texto.',
            'folder.mimes' => 'Apenas arquivos JPG,PNG e PDF são permitidos.',
            'folder.max' => 'O arquivo é muito grande, dimiua o arquivo usando www.ilovepdf.com/pt/comprimir_pdf ou www.tinyjpg.com.',
            'thumb.mimes' => 'Apenas arquivos JPG,PNG são permitidos.',
            'thumb.max' => 'O arquivo é muito grande, dimiua o arquivo usando www.tinyjpg.com.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Erro de validação', [
            'user' => auth()->user(),
            'request' => $this->all(),
            'uri' => $this->fullUrl(),
            'method' => static::class,
            'errors' => $validator->errors(),
        ]);

        parent::failedValidation($validator);
    }
}
