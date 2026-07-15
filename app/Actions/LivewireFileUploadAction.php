<?php

namespace App\Actions;

use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class LivewireFileUploadAction
{
    /**
     * Processa e salva o arquivo enviado via Livewire
     * retorna o nome do arquivo para salvar no banco
     *
     * @param TemporaryUploadedFile|null $file
     * @param string $path
     * @param bool|array $params
     * @return string|null $file_name
     */
    public static function handle(?TemporaryUploadedFile $file, string $path, bool|array $params = false): ?string
    {
        if (!$file) {
            return null;
        }

        $original_name = $file->getClientOriginalName();
        $file_name = pathinfo($original_name, PATHINFO_FILENAME);
        $file_name = Str::slug($file_name);
        $extension = strtolower($file->getClientOriginalExtension());

        // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
        if (in_array($extension, ['jpg', 'png', 'jpeg', 'webp'])) {
            $destinationPath = public_path($path);
            
            // Garante que o diretório existe
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $img = Image::decode($file);

            if (isset($params['height']) || isset($params['width'])) {
                $height = $params['height'] ?? null;
                $width = $params['width'] ?? null;

                $img->scaleDown(width: $width, height: $height);
            }

            $file_name = $file_name . '_' . time() . '.jpg';
            $img->save($destinationPath . '/' . $file_name, quality: 75);
            return $file_name;
        }

        if (in_array($extension, ['pdf', 'doc', 'docx'])) {
            $file_name = $file_name . '_' . time() . '.' . $extension;
            $destinationPath = public_path($path);
            
            // Garante que o diretório existe
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            // Copia o arquivo temporário para o destino
            copy($file->getRealPath(), $destinationPath . '/' . $file_name);
            return $file_name;
        }

        return null;
    }
}
