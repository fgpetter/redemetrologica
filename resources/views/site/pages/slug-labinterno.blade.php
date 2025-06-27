@extends('site.layouts.layout-site')
@section('title') {{ $laboratorio_interno->laboratorio->pessoa->nome_razao }} @endsection
@section('content')
  {{-- banner --}}
  <div class="SiteCards__bgimage p-5 text-center text-white"
    style="background-size: cover; background-image: url('{{ asset('build/images/site/BANNER-HOME-TOPO-2698-x-726-px_5-1.png') }}');">
    <div class="container">
      <h1 class="text-white">{{ $laboratorio_interno->laboratorio->pessoa->nome_razao }}</h1>
      <p class="lead">{{ $laboratorio_interno->laboratorio->nome_laboratorio ?? 'Laboratório Reconhecido' }}</p>
    </div>
  </div>
  {{-- banner --}}

  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-header bg-light py-3">
            <h5 class="card-title mb-0">Detalhes do Laboratório</h5>
          </div>
          <div class="card-body p-4">
            <dl class="row">
              <dt class="col-sm-4">Área de Atuação:</dt>
              <dd class="col-sm-8">{{ $laboratorio_interno->area->descricao }}</dd>

              @if ($laboratorio_interno->responsavel_tecnico)
                <dt class="col-sm-4">Responsável Técnico:</dt>
                <dd class="col-sm-8">{{ $laboratorio_interno->responsavel_tecnico }}</dd>
              @endif

              @if ($laboratorio_interno->telefone)
                <dt class="col-sm-4">Telefone:</dt>
                <dd class="col-sm-8">{{ $laboratorio_interno->telefone }}</dd>
              @endif

              @if ($laboratorio_interno->email)
                <dt class="col-sm-4">E-mail:</dt>
                <dd class="col-sm-8">{{ $laboratorio_interno->email }}</dd>
              @endif
            </dl>

            @if ($laboratorio_interno->certificado)
              <hr class="my-4">
              <div class="text-center">
                <a href="{{ asset('laboratorios-certificados/' . $laboratorio_interno->certificado) }}" target="_blank"
                  class="btn btn-primary px-4">
                  <i class="ph-file-arrow-down align-middle me-1"></i>
                  Baixar Certificado
                </a>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
