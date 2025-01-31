@extends('errors::layout')

@section('title', __('Server Error'))
@section('code', '500')
@section('message')

<div class="main-error">
  <h4>Erro ao processar sua requisição</h4>
</div>
<div class="text-error">
  <p class="text-md">
    Não foi possível processar os dados enviados. <br> 
    Um relatório com o erro foi enviado para análise da equipe técnica.
  </p>
  <p class="text-sm">
    Para mais informações entre em contato através do email <a href="mailto:ti@redemetrologica.com.br">ti@redemetrologica.com.br</a>
  </p>
</div>


@endsection