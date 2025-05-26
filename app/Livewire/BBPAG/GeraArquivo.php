<?php

namespace App\Livewire\BBPAG;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use App\Models\LancamentoFinanceiro;

class GeraArquivo extends Component
{
  public $dataInicio;
  public $dataFim;
  public $lancamentos;
  public $arquivoGerado;

  public function gerarArquivo()
  {
    $nomeEmpresa = str_pad('ASSOCIACAO REDE DE METROLOGIA', 30);      // 30 posições
    $ie          = str_pad('12345678901234', 14);                  // 14 posições
    $cnpj        = str_pad('97130207000112', 14, '0', STR_PAD_LEFT); // 14 posições, zeros à esquerda
    $cidade   = str_pad('PORTO ALEGRE', 20);   // 20 posições
    $endereco = str_pad('RUA SANTA CATARINA', 30); // 30 posições
    $numero   = str_pad('40', 5, '0', STR_PAD_LEFT);   // 5 posições, zeros à esquerda
    $uf       = str_pad('RS', 2);                // 2 posições
    $cep      = str_pad('91030330', 8);          // 8 posições
    $agencia    = str_pad('000108', 6, '0', STR_PAD_LEFT);       // 6 posições
    $conta      = str_pad('0000000050237', 13, '0', STR_PAD_LEFT); // 13 posições
    $dvAgenciaCC = ' ';  // dígito verificador conjunto agência/conta em branco
    $data = date('dmYHis');

    // Construção do HEADER DE ARQUIVO (falta nº e codigo de convenio com o banco. Fica entre cnpj e agencia)
    $HeaderArquivo = '00100000         2' . $cnpj . '                    ' . $agencia . $conta . $dvAgenciaCC . $nomeEmpresa . 'BANCO DO BRASIL                         1' . $data . '00000103000000                                                      000            ' . "\r\n";

    // Construção do HEADER DE LOTE
    $HeaderLote = '00100011C2001020 2' . $cnpj . '0007501030126       ' . $agencia . $conta . $dvAgenciaCC . $nomeEmpresa . '                                        ' . $endereco . $numero . '               ' . $cidade . $cep . $uf . '                  ' . "\r\n";

    $this->arquivoGerado = $HeaderArquivo;
    $this->arquivoGerado .= $HeaderLote;

    // Loop para gerar SEGMENTO A e SEGMENTO B para cada lançamento
    $contador = 1;
    foreach ($this->lancamentos as $lancamento) {
      $dadosBancarios = $lancamento->pessoa->dadoBancario;

      $banco = str_pad($dadosBancarios->cod_banco ?? '001', 3, '0', STR_PAD_LEFT);
      $agencia = str_pad(explode('-', $dadosBancarios->agencia ?? '12345')[0], 5, '0', STR_PAD_LEFT);
      $dvAgencia = str_pad(explode('-', $dadosBancarios->agencia ?? '12345')[1] ?? '0', 1, '0', STR_PAD_LEFT);
      $conta = str_pad(explode('-', $dadosBancarios->conta ?? '123456789012')[0], 12, '0', STR_PAD_LEFT);
      $dvConta = str_pad(explode('-', $dadosBancarios->conta ?? '123456789012')[1] ?? '0', 1, '0', STR_PAD_LEFT);
      $nome = str_pad(strtoupper(substr(preg_replace('/[^A-Za-z0-9 ]/', '', $lancamento->pessoa->nome_razao ?? 'NOME FICTICIO'), 0, 30)), 30);
      $seuNumero = str_pad($lancamento->id ?? '0', 20, '0', STR_PAD_LEFT);
      $dataPagamento = str_pad(date('dmY', strtotime($lancamento->data_vencimento ?? 'now')), 8, ' ');
      $valor = str_pad(number_format($lancamento->valor ?? 0, 2, '', ''), 15, '0', STR_PAD_LEFT);
      $tipo = ($lancamento->pessoa->tipo_pessoa === 'PJ') ? '2' : '1';
      $cpfCnpj = str_pad(preg_replace('/\D/', '', $lancamento->pessoa->cpf_cnpj ?? '00000000000'), 14, '0', STR_PAD_LEFT);

      $segmentoA = '00100013' . str_pad($contador++, 5, '0', STR_PAD_LEFT) . 'A000000' . $banco . $agencia . $dvAgencia . $conta . $dvConta . ' ' . $nome . $seuNumero . $dataPagamento . 'BRL000000000000000' . $valor . '                    00000000000000000000000                                                    0          ' . "\r\n";
      $segmentoB = str_pad('00100013' . str_pad($contador++, 5, '0', STR_PAD_LEFT) . 'B   ' . $tipo . $cpfCnpj, 240, ' ', STR_PAD_RIGHT) . "\r\n";

      $this->arquivoGerado .= $segmentoA;
      $this->arquivoGerado .= $segmentoB;
    }

    // Construção do TRAILER DE LOTE
    $contador += 1; // Incrementa para incluir o header de lote
    $totalRegistrosLote = str_pad($contador, 6, '0', STR_PAD_LEFT);
    $somaValores = str_pad(number_format($this->lancamentos->sum('valor'), 2, '', ''), 18, '0', STR_PAD_LEFT);
    $fimTrailerLote = '000000000000000000' . str_pad('', 222 - strlen('00100015         ' . $totalRegistrosLote . $somaValores), ' ');
    $trailerLote = '00100015         ' . $totalRegistrosLote . $somaValores . $fimTrailerLote . "\r\n";

    $this->arquivoGerado .= $trailerLote;

    // Construção do TRAILER DE ARQUIVO
    $contador += 2; // Incrementa para incluir o header e trailer de arquivo 
    $totalRegistrosArquivo = str_pad($contador, 6, '0', STR_PAD_LEFT);
    $fimTrailerArquivo = '000000' . str_pad('', 234 - strlen('00199999         000001' . $totalRegistrosArquivo), ' ');
    $trailerArquivo = '00199999         000001' . $totalRegistrosArquivo . $fimTrailerArquivo;

    $this->arquivoGerado .= $trailerArquivo;

    // Após gerar o conteúdo do arquivo, converte para o formato ANSI
    $this->arquivoGerado = mb_convert_encoding($this->arquivoGerado, 'ISO-8859-1', 'UTF-8');
  }

  public function filtrarLancamentos()
  {
    $validated = $this->validate([
      'dataInicio' => ['nullable', 'date'],
      'dataFim' => ['nullable', 'date'],
    ]);

    $this->lancamentos = LancamentoFinanceiro::query()
      ->with(['pessoa.dadoBancario' => function ($query) {}])
      ->when($validated['dataInicio'], function ($query, $dataInicio) {
        $query->where('data_vencimento', '>=', $dataInicio);
      })
      ->when($validated['dataFim'], function ($query, $dataFim) {
        $query->where('data_vencimento', '<=', $dataFim);
      })
      ->orderBy('data_vencimento')
      ->get();
  }

  public function baixarArquivo()
  {
    $dataInicio = $this->dataInicio ? date('dmY', strtotime($this->dataInicio)) : 'inicio';
    $dataFim = $this->dataFim ? date('dmY', strtotime($this->dataFim)) : 'fim';
    $nomeArquivo = "remessa_{$dataInicio}_ate_{$dataFim}.txt";

    $caminhoArquivo = storage_path("app/public/{$nomeArquivo}");
    file_put_contents($caminhoArquivo, $this->arquivoGerado);

    return response()->download($caminhoArquivo)->deleteFileAfterSend(true);
  }

  public function render()
  {
    return view('livewire.bbpag.gera-arquivo');
  }
}
