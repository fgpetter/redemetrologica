<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;

class ImageUploadController extends Controller
{
    /**
     * Salva imagem temporária do CKEditor e retorna a URL.
     */
    public function store(Request $request): JsonResponse
    {
        if (! $request->hasFile('upload')) {
            return response()->json(['uploaded' => 0, 'error' => ['message' => 'Nenhum arquivo enviado.']], 422);
        }

        $originName = $request->file('upload')->getClientOriginalName();
        $fileName = pathinfo($originName, PATHINFO_FILENAME);
        $fileName = str_replace(' ', '_', $fileName);
        $extension = $request->file('upload')->getClientOriginalExtension();
        $fileName = $fileName.'_'.time().'.'.$extension;
        $pastaTemp = 'Temp'.substr(hrtime(true), -9, 9);

        $tempPastas = $request->session()->get('tempPastas', []);
        $tempPastas[] = $pastaTemp;
        $request->session()->put('tempPastas', $tempPastas);

        $request->file('upload')->move(public_path($pastaTemp), $fileName);

        $img = Image::decodePath(public_path($pastaTemp.'/'.$fileName));
        $img->scaleDown(width: 750, height: 750);
        $img->encodeUsingFormat(Format::JPEG, quality: 75)
            ->save(public_path($pastaTemp.'/'.$fileName));

        return response()->json([
            'fileName' => $fileName,
            'uploaded' => 1,
            'url' => asset($pastaTemp.'/'.$fileName),
        ]);
    }
}
