
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
            <div class="row">

              <div class="col-sm-4">
                <h6>Laboratório:</h6>
              </div>
              <div class="col-sm-8">
                <p>{{ $laboratorio_interno->laboratorio->nome_laboratorio ?? $laboratorio_interno->laboratorio->pessoa->nome_razao }}</p>
              </div>

              @if ($laboratorio_interno->laboratorio->pessoa->enderecos()->first())
                <div class="col-sm-4">
                  <h6>Endereço:</h6>
                </div>
                <div class="col-sm-8">
                  <p>
                    {{ $laboratorio_interno->laboratorio->pessoa->enderecos()->first()->endereco }},
                    {{ $laboratorio_interno->laboratorio->pessoa->enderecos()->first()->numero }} - 
                    {{ $laboratorio_interno->laboratorio->pessoa->enderecos()->first()->complemento }} 
                  <br>
                    {{ $laboratorio_interno->laboratorio->pessoa->enderecos()->first()->bairro }}, 
                    {{ $laboratorio_interno->laboratorio->pessoa->enderecos()->first()->cidade }} /
                    {{ $laboratorio_interno->laboratorio->pessoa->enderecos()->first()->uf }} -
                    {{ $laboratorio_interno->laboratorio->pessoa->enderecos()->first()->cep }}
                  </p>
                </div>
              @endif

              <div class="col-sm-4">
                <h6>Contato:</h6>
              </div>
              <div class="col-sm-8">
                <p>
                  {{ $laboratorio_interno->laboratorio->contato }}
                </p>
              </div>

              <div class="col-sm-4">
                <h6>Responsável Técnico:</h6>
              </div>
              <div class="col-sm-8">
                <p>
                  {{ $laboratorio_interno->laboratorio->responsavel_tecnico }}
                </p>
              </div>

              <div class="col-sm-4">
                <h6>Telefone:</h6>
              </div>
              <div class="col-sm-8">
                <p>
                  {{ $laboratorio_interno->laboratorio->telefone }}
                </p>
              </div>

              <div class="col-sm-4">
                <h6>E-mail:</h6>
              </div>
              <div class="col-sm-8">
                <p>
                  {{ $laboratorio_interno->laboratorio->email }}
                </p>
              </div>

              <div class="col-sm-4">
                <h6>CNPJ:</h6>
              </div>
              <div class="col-sm-8">
                <p>{{ $laboratorio_interno->laboratorio->pessoa->cpf_cnpj }}</p>
              </div>

              <hr class="my-4">
              <h5>LABORATÓRIO INTERNO</h5>
              <div class="col-sm-4">
                <h6>Área de Atuação:</h6>
              </div>
              <div class="col-sm-8">
                <p>{{ $laboratorio_interno->area->descricao }}</p>
              </div>

              @if ($laboratorio_interno->responsavel_tecnico)
                <div class="col-sm-4">
                  <h6>Responsável Técnico:</h6>
                </div>
                <div class="col-sm-8">
                  <p>{{ $laboratorio_interno->responsavel_tecnico }}</p>
                </div>
              @endif

              @if ($laboratorio_interno->telefone)
                <div class="col-sm-4">
                  <h6>Telefone:</h6>
                </div>
                <div class="col-sm-8">
                  <p>{{ $laboratorio_interno->telefone }}</p>
                </div>
              @endif

              @if ($laboratorio_interno->email)
                <div class="col-sm-4">
                  <h6>E-mail:</h6>
                </div>
                <div class="col-sm-8">
                  <p>{{ $laboratorio_interno->email }}</p>
                </div>
              @endif
            </div>

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
