<?php

namespace App\Http\Requests;

use App\Actions\Suporte\ArquivosSuporteAction;
use App\Models\SuporteDemanda;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class UploadImagemSuporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        SuporteDemanda::autorizarAcesso($this->user());

        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['upload' => ArquivosSuporteAction::regras(true)];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'upload.max' => 'Cada imagem deve ter no máximo 5 MB.',
            'upload.mimes' => 'Envie uma imagem JPG, PNG, GIF ou WebP.',
            'upload.extensions' => 'Envie uma imagem JPG, PNG, GIF ou WebP.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::channel('validation')->info('Upload inválido no suporte', ['user' => $this->user()?->id, 'errors' => $validator->errors()->toArray()]);
        throw new HttpResponseException(response()->json(['uploaded' => 0, 'error' => ['message' => $validator->errors()->first()]], 422));
    }
}
