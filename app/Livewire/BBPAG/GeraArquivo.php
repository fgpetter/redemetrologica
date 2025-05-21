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

    // Construção do HEADER DE ARQUIVO
    $HeaderArquivo = '00100000         2' . $cnpj . '                    ' . $agencia . $conta . $dvAgenciaCC . $nomeEmpresa . 'BANCO DO BRASIL                         1' . $data . '00000103000000                                                      000            ' . "\r\n";

    // Construção do HEADER DE LOTE
    $HeaderLote = '00100011C2001020 2' . $cnpj . '0007501030126       ' . $agencia . $conta . $dvAgenciaCC . $nomeEmpresa . '                                        ' . $endereco . $numero . '               ' . $cidade . $cep . $uf . '                  ' . "\r\n";

    $this->arquivoGerado = $HeaderArquivo;
    $this->arquivoGerado .= $HeaderLote;

    // Loop para gerar SEGMENTO A e SEGMENTO B para cada lançamento
    $contador = 1;
    foreach ($this->lancamentos as $lancamento) {
        $banco = str_pad('001', 3, '0', STR_PAD_LEFT);
        $agencia = str_pad(substr($lancamento['banco_agencia'] ?? '12345', 0, -1), 5, '0', STR_PAD_LEFT);
        $dvAgencia = str_pad(substr($lancamento['banco_agencia'] ?? '12345', -1), 1, '0', STR_PAD_LEFT);
        $conta = str_pad(substr($lancamento['banco_conta'] ?? '123456789012', 0, -1), 12, '0', STR_PAD_LEFT);
        $dvConta = str_pad(substr($lancamento['banco_conta'] ?? '123456789012', -1), 1, '0', STR_PAD_LEFT);
        $nome = str_pad(strtoupper(substr($lancamento['nome'] ?? 'NOME FICTICIO', 0, 30)), 30);
        $seuNumero = str_pad($lancamento['id'] ?? '0', 20, '0', STR_PAD_LEFT);
        $dataPagamento = str_pad(date('dmY', strtotime($lancamento['data_vencimento'] ?? 'now')), 8, ' ');
        $valor = str_pad(number_format($lancamento['valor'] ?? 0, 2, '', ''), 15, '0', STR_PAD_LEFT);
        $tipo = $lancamento['tipo_classificacao'] ?? '1';
        $cpfCnpj = str_pad(preg_replace('/\D/', '', $lancamento['cpf_cnpj'] ?? '00000000000'), 14, '0', STR_PAD_LEFT);

        $segmentoA = '00100013' . str_pad($contador++, 5, '0', STR_PAD_LEFT) . 'A000000' . $banco . $agencia . $dvAgencia . $conta . $dvConta . ' ' . $nome . $seuNumero . $dataPagamento . 'BRL000000000000000' . $valor . '                    00000000000000000000000                                                    0          ' . "\r\n";
        $segmentoB = str_pad('00100013' . str_pad($contador++, 5, '0', STR_PAD_LEFT) . 'B   ' . $tipo . $cpfCnpj, 240, ' ', STR_PAD_RIGHT) . "\r\n";

        $this->arquivoGerado .= $segmentoA;
        $this->arquivoGerado .= $segmentoB;
    }
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
