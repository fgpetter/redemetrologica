<?php

namespace App\Http\Controllers;

use App\Helpers\Telefone;
use App\Http\Requests\FaleconoscoRequest;
use App\Mail\FaleconoscoMail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class FaleconoscoController extends Controller
{
    /**
     * Gera pagina de Fale-conosco
     *
     **/
    public function index(): View
    {
        return view('site.pages.fale-conosco');
    }

    public function enviar(FaleconoscoRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Sanitização dos dados validados
        $validated['name'] = strip_tags(trim($validated['name']));
        $validated['email'] = filter_var(trim($validated['email']), FILTER_SANITIZE_EMAIL);
        $validated['phone'] = Telefone::somenteNumeros($validated['phone']);

        if (isset($validated['message'])) {
            $validated['message'] = strip_tags(trim($validated['message']));
        }

        if (isset($validated['areas'])) {
            $validated['areas'] = array_map(function ($area) {
                return strip_tags(trim($area));
            }, $validated['areas']);
        }

        Mail::to('contato@redemetrologica.com.br')->send(new FaleconoscoMail($validated));

        return redirect()->route('faleconosco.form')
            ->with('success');
    }
}
