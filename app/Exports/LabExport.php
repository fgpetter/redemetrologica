<?php

namespace App\Exports;

use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LabExport implements FromView
{

    public function __construct(AgendaInterlab $agendainterlab) {}

    public function view(): View
    {
        return view('excel.enderecos-laboratorios',[
            'inscritos' => InterlabInscrito::where('agenda_interlab_id', 3)
              ->with(['pessoa', 'empresa', 'laboratorio.endereco'])
              ->get()
          ]);
    }
}