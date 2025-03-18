@extends('site.layouts.layout-site')
@section('title') Interlaboratoriais @endsection
@section('content')
  {{-- banner inicial --}}
  <div class="card text-bg-dark">
    <img src="{{ asset('build\images\site\PEP-BANNER-DE-TOPO-1920-x-575-px_B.png') }}" class="card-img" alt="...">

    <div class="card-img-overlay d-flex justify-content-center">
      <div class="align-self-center text-center ">
        <p class="SiteBanner--interlab"><strong>INTERLABORATORIAIS</strong></p>
      </div>
    </div>

  </div>
  {{-- banner inicial --}}

  {{-- baixar --}}
  <div class="container my-5">
    <div class="row  d-flex align-items-center justify-content-center text-center">
      <div class="col">
        <a href="{{ asset('downloads/Previsao_Ensaios_de_Proficiencia_2025.pdf') }}" class="link-secondary link-offset-2 text-decoration-underline">
          <h4>CLIQUE AQUI PARA BAIXAR A PROGRAMAÇÃO ANUAL 
          <i class="ri-file-download-line"></i>
          </h4>
        </a>
      </div>
    </div>
  </div>
  {{-- baixar --}}

  {{-- interlab --}}
<div class="container">
  <div class="row gy-5">
    @foreach ($interlabs as $agendaInterlab)
    <div class="col-12 col-sm-6 col-lg-3"> 
      <div class="card ribbon-box border shadow-none mb-lg-0 card-interlab h-100">
        @if( $agendaInterlab->inscricao == 1 )
          <div class="ribbon ribbon-primary round-shape">Inscrições abertas</div>
        @endif
        <img src="{{ url( asset('build/images/site/').'/'.$agendaInterlab->interlab->thumb ) }}" 
          class="card-img-top align-self-center pt-2 img-fluid" alt="" style="max-width: 170px"> 
        <div class="card-body text-white d-flex flex-column overflow-hidden" 
             style="background-color: #405D71; margin-top: -15px; height: 150px;">
          <a href="{{ route('site-single-interlaboratorial', $agendaInterlab->uid)}}" 
             class="text-white bold flex-grow-1 overflow-hidden">
            <h5 class="card-title pb-2">{{ $agendaInterlab->interlab->nome }}</h5>
          </a>

          <a href="{{ route('site-single-interlaboratorial', $agendaInterlab->uid)}}" 
             class="text-start text-white bold mt-auto">Visualizar <i
            class="fa-solid fa-circle-chevron-right"></i></a>
        </div>

        <div class="card-footer py-2 border-0 text-white" style="background-color: #002C41">
          <i class="bi bi-calendar2-event"></i> &nbsp; 
            @if($agendaInterlab->data_inicio) {{ \Carbon\Carbon::parse($agendaInterlab->data_inicio)->format('d/m/Y') }} @endif 
        </div>
      </div>
    </div>
    @endforeach
  </div>
</div>
  {{-- interlab --}}

@endsection
