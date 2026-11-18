@extends('layouts.email')

@section('content')
<h3>Relatório de Certificados Removidos</h3>
<p>O comando de limpeza de certificados foi executado. Os seguintes arquivos foram removidos:</p>

@if(!empty($deletedFiles))
  <ul style="padding-left: 20px;">
    @foreach($deletedFiles as $file)
      <li style="margin-bottom: 4px;">{{ $file }}</li>
    @endforeach
  </ul>
  <p>Total: <strong>{{ count($deletedFiles) }} arquivo(s)</strong> removido(s).</p>
@else
  <p>Nenhum arquivo foi removido nesta execução.</p>
@endif

<br>
<p>
  Atenciosamente,<br>
  Sistema Rede Metrológica RS
</p>
@endsection
