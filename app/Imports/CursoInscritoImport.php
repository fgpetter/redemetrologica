<?php

namespace App\Imports;

use App\Models\Pessoa;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CursoInscritoImport implements ToCollection
{

    public function __construct(public AgendaCursos $agendacurso) {}

    /**
     * Lê o arquivo XLSX e insere inscritos no curso
     *
     * @param Collection $rows
     * @return void
     */
    public function collection(Collection $rows): void
    {
        $header = $rows->first()->toArray();
        $data = $rows->slice(1)->map(function ($row) use ($header) {
            return array_combine($header, $row->toArray());
        })->toArray();

        foreach($data as $item){
            $pessoa = Pessoa::firstOrCreate(
              [ 'cpf_cnpj' => $item['cpf_cnpj'] ],
              [
                'nome_razao' => $item['nome_razao'],
                'email' => $item['email'],
                'tipo_pessoa' => 'PF'
              ]
            );
            CursoInscrito::create([
              'pessoa_id' => $pessoa->id,
              'empresa_id' => $this->agendacurso->empresa_id,
              'agenda_curso_id' => $this->agendacurso->id,
              'data_inscricao' => now()
            ]);
          }
    }
}