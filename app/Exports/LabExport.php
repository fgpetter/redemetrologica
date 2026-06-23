<?php

namespace App\Exports;

use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LabExport implements FromView
{
    public function __construct(public AgendaInterlab $agendainterlab) {}

    public function view(): View
    {
        $this->agendainterlab->loadMissing('interlab');

        $query = InterlabInscrito::where('agenda_interlab_id', $this->agendainterlab->id)
            ->with(['pessoa', 'empresa', 'laboratorio.endereco']);

        if ($this->agendainterlab->exportaInscritosPorAnalista()) {
            $query->with('analistas')->whereHas('analistas');
        }

        return view('excel.enderecos-laboratorios', [
            'inscritos' => $query->get(),
            'agendainterlab' => $this->agendainterlab,
        ]);
    }
}
