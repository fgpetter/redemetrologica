<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class UploadMaterialTreinamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'arquivo' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'extensions:jpg,jpeg,png,pdf,doc,docx', 'max:5120', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value instanceof UploadedFile) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'], true)
                        && ! \Illuminate\Support\Facades\Validator::make(['file' => $value], ['file' => 'mimes:'.$extension])->passes()) {
                        $fail('A extensão não corresponde ao conteúdo do arquivo.');
                    }
                }
            }],
            'descricao' => ['nullable', 'string', 'max:190'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $arquivo = $this->file('arquivo');

        Log::channel('validation')->info('Erro de validação de material de treinamento', [
            'user_id' => $this->user()?->id,
            'agenda_curso_uid' => $this->route('agendaCurso')?->uid,
            'arquivo' => $arquivo instanceof UploadedFile ? $arquivo->getClientOriginalName() : null,
            'errors' => $validator->errors()->toArray(),
        ]);

        session()->flash('error', 'Revise os dados informados.');
        parent::failedValidation($validator);
    }
}
