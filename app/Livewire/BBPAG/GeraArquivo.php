<?php

namespace App\Livewire\BBPAG;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use App\Models\LancamentoFinanceiro;

class GeraArquivo extends Component
{
    public $dataInicio;
    public $dataFim;
    public $lancamentos = [];
    public $arquivoGerado;

    public function gerarArquivo()
    {
      $nomeEmpresa = str_pad('EMPRESA FICTICIA', 30);
          $ie = str_pad('12345678901234', 14);
          $cnpj = str_pad('12345678000195', 14, '0', STR_PAD_LEFT);
          $cidade = str_pad('CIDADE FICTICIA', 20);
          $endereco = str_pad('RUA FICTICIA', 30);
          $numero = str_pad('123', 5, '0', STR_PAD_LEFT);
          $uf = str_pad('SP', 2);
  
          $cep = str_pad('12345678', 8);
          $agencia = str_pad('123456', 6, '0', STR_PAD_LEFT);
          $conta = str_pad('1234567890123', 13, '0', STR_PAD_LEFT);
          $dvAgenciaCC = ' ';
          $data = date('dmYHis');


    // Lógica para gerar o arquivo com base nos lançamentos filtrados e a seguinte estrutura:

    // 1 linha com HEADER DE ARQUIVO
      // Construção do HEADER DE ARQUIVO
      $HeaderArquivo = '00100000         2' . $cnpj . '                    ' . $agencia . $conta . $dvAgenciaCC . $nomeEmpresa . 'BANCO DO BRASIL                         1' . $data . '00000103000000                                                      000            ' . "\r\n";
    // 1 linha com HEADER DE LOTE (lançamentos)
    // loop para cada lancamento (N Linhas)
    //  --  1 linha com SEGMENTO A - Lançamento
    //  --  1 linha com SEGMENTO B - Lançamento
    // 1 linha com TRAILER DE lote
    // 1 linha com TRAILER DE ARQUIVO
    // Dados fictícios para placeholders


        $this->arquivoGerado = $HeaderArquivo;
        $this->arquivoGerado .= $HeaderArquivo;
    }

    public function filtrarLancamentos()
    {
        $validated = $this->validate([
            'dataInicio' => ['nullable', 'date'],
            'dataFim' => ['nullable', 'date'],
        ]);

        $this->lancamentos = LancamentoFinanceiro::query()
            ->when($validated['dataInicio'], function ($query, $dataInicio) {
                $query->where('data_vencimento', '>=', $dataInicio);
            })
            ->when($validated['dataFim'], function ($query, $dataFim) {
                $query->where('data_vencimento', '<=', $dataFim);
            })
            ->orderBy('data_vencimento')
            ->get()
            ->toArray();
    }

    public function baixarArquivo()
    {
        // Lógica para baixar o arquivo gerado
        return Storage::download($this->arquivoGerado);
    }

    public function render()
    {
        return view('livewire.bbpag.gera-arquivo');
    }
}
