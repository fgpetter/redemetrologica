<?php

namespace App\Exports;

use App\Models\AgendaCursos;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CursoInscritosExport implements FromView
{
  public function __construct(public AgendaCursos $agendacurso) {}

  public function view(): View
  {
    return view('excel.lista-presenca-curso', [
      'agendacurso' => $this->agendacurso->load(['curso', 'inscritos.pessoa', 'inscritos.empresa'])
    ]);
  }
} 