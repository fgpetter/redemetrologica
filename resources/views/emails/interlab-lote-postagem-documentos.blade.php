@extends('layouts.email')

@section('content')
<h3>Documentos do lote de postagem</h3>

<p>O lote <strong>{{ $lote->nome }}</strong> ({{ $lote->uid }}) foi concluído com sucesso.</p>

<p>
  Seguem em anexo os PDFs das etiquetas e o PDF de conteúdo do lote.
</p>

<br>
<p>
  Atenciosamente,<br>
  Sistema Rede Metrológica RS
</p>
@endsection
