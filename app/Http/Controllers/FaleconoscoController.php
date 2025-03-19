<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\FaleconoscoMail;

class FaleconoscoController extends Controller
{
    /**
   * Gera pagina de Fale-conosco
   *
   * @return View
   **/
    public function index()
    {
        return view('site.pages.fale-conosco');
    }


    public function enviar(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string|max:2000',
            'areas' => 'sometimes|array',
            'areas.*' => 'string'
        ]);

        // Sanitização dos dados validados
        $validated['name'] = strip_tags(trim($validated['name']));
        $validated['email'] = filter_var(trim($validated['email']), FILTER_SANITIZE_EMAIL);
        $validated['phone'] = strip_tags(trim($validated['phone']));
    
        if (isset($validated['message'])) {
        $validated['message'] = strip_tags(trim($validated['message']));
    }

        if (isset($validated['areas'])) {
        $validated['areas'] = array_map(function($area) {
            return strip_tags(trim($area));
        }, $validated['areas']);
    }

        Mail::to('representante@redemetrologica.com.br')->send(new FaleconoscoMail($validated));

        return redirect()->route('faleconosco.form')
            ->with('success', 'Mensagem enviada com sucesso!');
    }
}