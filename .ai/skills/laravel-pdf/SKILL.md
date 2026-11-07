---
name: laravel-pdf
description: Gerar, formatar, salvar e testar PDFs em Laravel com spatie/laravel-pdf usando Cloudflare ou DOMPDF no sistema-rede.
---

# Laravel PDF

Use esta skill para gerar PDFs a partir de views Blade ou HTML no sistema-rede. Consulte a [documentação do Spatie Laravel PDF v2](https://spatie.be/docs/laravel-pdf/v2/introduction) para detalhes da API e confirme a versão instalada antes de usar recursos específicos.

## Drivers do projeto

- **Cloudflare** é o padrão em `config/laravel-pdf.php`, sujeito à configuração carregada no ambiente. Usa a API Browser Rendering e requer `CLOUDFLARE_API_TOKEN` e `CLOUDFLARE_ACCOUNT_ID`. Suporta opções de papel, margens, orientação e views ou HTML de cabeçalho e rodapé. A saída é PDF.
- **DOMPDF** está instalado no projeto e pode ser selecionado por PDF com `->driver('dompdf')`. Trabalha com CSS 2.1 e parte do CSS 3, sem flexbox, grid ou execução de JavaScript. O driver insere cabeçalho e rodapé no início e no fim do conteúdo, sem repeti-los automaticamente em cada página. As diretivas `@pageNumber` e `@totalPages` não são suportadas por esse driver.
- O acesso remoto do DOMPDF está desabilitado por padrão na configuração do projeto. Verifique imagens, fontes e CSS externos e o `chroot` configurado para arquivos locais.

Confira o driver usado no fluxo existente antes de alterá-lo. As Actions de relatórios de avaliação selecionam DOMPDF explicitamente, enquanto os certificados em `DocController` usam o driver configurado.

## Criar e salvar

```php
use Spatie\LaravelPdf\Facades\Pdf;

Pdf::view('pdf.relatorio', ['dados' => $dados])
    ->driver('dompdf')
    ->format('a4')
    ->save($caminhoAbsoluto);
```

Use `Pdf::html($html)` para HTML direto. Para salvar em um disco Laravel, use `->disk('nome-do-disco')->save('caminho/arquivo.pdf')`. O método `base64()` retorna o conteúdo codificado.

As Actions de relatórios existentes salvam em um arquivo temporário e retornam `response()->download(...)->deleteFileAfterSend()`. Certificados salvos para reutilização seguem o fluxo de `DocController`. Preserve o ciclo de vida e o disco do fluxo que estiver sendo alterado.

Para devolver o PDF diretamente pela API do pacote:

```php
use function Spatie\LaravelPdf\Support\pdf;

return pdf()
    ->view('pdf.relatorio', ['dados' => $dados])
    ->name('relatorio.pdf')
    ->download();
```

Sem `download()`, a resposta é exibida inline.

## Formatação

Use `format('a4')`, `landscape()`, `margins(top: 15, right: 10, bottom: 15, left: 10, unit: 'mm')` e `paperSize(57, 500, 'mm')` conforme necessário. Cabeçalhos e rodapés podem usar `headerView()` e `footerView()` ou `headerHtml()` e `footerHtml()`. A paginação e a repetição dependem do driver.

## Testes

Use `Pdf::fake()` e as asserções do pacote para verificar a view, dados e caminho. Para verificar conteúdo renderizado em um callback, use `$pdf->contains('texto')`.

```php
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

Pdf::fake();

// Executar a ação que gera o PDF.

Pdf::assertViewIs('pdf.relatorio');
Pdf::assertSaved(fn (PdfBuilder $pdf, string $path) => $path === $caminhoAbsoluto
    && $pdf->contains('Relatório'));
```

Para respostas devolvidas diretamente pelo builder, use `Pdf::assertRespondedWithPdf()`. Nos fluxos que salvam um arquivo e depois o retornam com `response()->download()`, siga os testes existentes que usam `Pdf::swap()` com um `FakePdfBuilder` adaptado para criar um arquivo de teste.

Os fakes verificam a integração e o conteúdo preparado. Quando a apresentação for requisito, gere um PDF com o driver escolhido e confira o resultado visual.
