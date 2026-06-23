<?php

namespace App\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class FileUploadAction
{
    /**
     * Processa e salva o arquivo na pasta correta
     * retorna o nome do arquivo para salvar no banco
     *
     * @return string|bool $file_name
     */
    public static function handle(Request $request, string $filename, string $path, bool|array $params = false): ?string
    {
        if ($request->hasFile($filename)) {

            $file = $request->file($filename);
            $original_name = $file->getClientOriginalName();
            $file_name = pathinfo($original_name, PATHINFO_FILENAME);
            $file_name = Str::slug($file_name);
            $extension = $file->getClientOriginalExtension();

            // Redimensionar e codificar a imagem para 'jpg' com 75% do tamanho original
            if ($extension == 'jpg' || $extension == 'png' || $extension == 'jpeg' || $extension == 'webp') {

                $img = Image::decode($file);

                if (isset($params['height']) || isset($params['width'])) {

                    $height = $params['height'] ?? null;
                    $width = $params['width'] ?? null;

                    if ($img->height() > $height || $img->width() > $width) {
                        $img->scaleDown(width: $width, height: $height);
                    }
                }

                $file_name = $file_name.'_'.time().'.jpg';

                $img->save(public_path($path.'/'.$file_name), quality: 75);

                return $file_name;
            }

            if ($extension == 'pdf' || $extension == 'doc' || $extension == 'docx') {

                $file_name = $file_name.'_'.time().'.'.$extension;

                $request->file($filename)->move(public_path($path), $file_name);

                return $file_name;

            }
        }

        return false;
    }
}
