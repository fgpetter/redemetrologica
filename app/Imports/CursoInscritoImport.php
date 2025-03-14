<?php

namespace App\Imports;

use App\Models\Pessoa;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use Illuminate\Support\Collection;
use App\Actions\CreateUserForPessoaAction;
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
  public function collection(Collection $rows)
  {
    $header = $rows->first()->toArray();
    $required = ['cpf_cnpj', 'nome_razao', 'email'];
    
    // Verifica se todas as colunas obrigatórias estão presentes
    if (count(array_intersect($required, $header)) !== count($required)) {
      return back()->with('error', 'O arquivo não contém todas as colunas obrigatórias.')->withFragment('participantes');
    }

    $data = $rows->slice(1)->map(function ($row) use ($header) {
      return array_combine($header, $row->toArray());
    })->toArray();

    foreach($data as $item){

      // Pula se todos os campos obrigatórios não estão vazios
      if(empty($item['cpf_cnpj']) || empty($item['nome_razao']) || empty($item['email'])){
        continue;
      }

      // Remove caracteres especiais e espaços extras
      $item['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $item['cpf_cnpj']);
      $item['nome_razao'] = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', trim($item['nome_razao']));
      
      // Pula se o e-mail for inválido
      if( isInvalidEmail( $item['email'] ) ){
        continue;
      }

      // Verifica se a pessoa já existe ou cria uma nova
      $pessoa = Pessoa::firstOrCreate(
        [ 'cpf_cnpj' => $item['cpf_cnpj'] ],
        [
          'nome_razao' => $item['nome_razao'],
          'email' => $item['email'],
          'tipo_pessoa' => 'PF'
        ]
      );

      // Cria o usuário para a pessoa
      CreateUserForPessoaAction::handle( $pessoa );

      // Cria o registro de inscrição
      CursoInscrito::create([
        'pessoa_id' => $pessoa->id,
        'empresa_id' => $this->agendacurso->empresa_id,
        'agenda_curso_id' => $this->agendacurso->id,
        'data_inscricao' => now()
      ]);
    }
  }
}