@extends('site.layouts.layout-site')
@section('content')
  <!-- {{-- banner inicial --}} -->
  <div class="p-5 text-center mb-0"
    style=" background-repeat: no-repeat; background-size: cover; background-image: url('{{ asset('build/images/site/LAB-SOLICITAR-RECONHECIMENTO-1349-x-443.png') }}'); width: 100%; ">

    <div class=" container align-self-center text-start  ">
      <p class="SiteBanner--titulo"><strong>{{ $agendainterlab->interlab->nome }}</strong></p>
    </div>
  </div>
  <!-- {{-- conteúdo --}} -->

    <div class="container bg-white shadow-lg rounded p-4">
      <div class="row">
        <div class="col pe-5">
          <div class="d-inline-block fs-5">
            {!! $agendainterlab->descricao !!}
          </div>
        </div>
        <div class="col-sm-4 text-center pt-5">
          <h4>INSCRIÇÕES ABERTAS</h4>
          <P class="fs-5">Data de inicio : {{ \Carbon\Carbon::parse($agendainterlab->data_inicio)->format('d/m/Y') }}</P>
          <button class="btn btn-lg btn-rede-azul mt-3">INSCREVA-SE</button>
        </div>
      </div>
    </div>

@endsection
