@extends('site.layouts.layout-site')
@section('title') Cursos @endsection
@section('content')
  {{-- carousel --}}
  <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="{{ asset('build\images\site\BANNER-CURSOS-DESTAQUE-2_B.png') }}" class="card-img" alt="...">
      </div>
      <div class="carousel-item">
        <img src="{{ asset('build\images\site\BANNER-CURSOS-DESTAQUE-3_B-1.png') }}" class="card-img" alt="...">
      </div>
      <div class="carousel-item">
        <img src="{{ asset('build\images\site\BANNER-CURSOS-DESTAQUE-1_B.png') }}" class="card-img" alt="...">
      </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>
  {{-- carousel --}}


  {{-- pesquisa --}}
  {{-- <div class="container my-5">
    <div class="btn-toolbar justify-content-start" role="toolbar" aria-label="Toolbar with button groups">
      <div class="input-group border">
        <input type="text" class="form-control" placeholder="PESQUISAR POR" aria-label="Input group example"
          aria-describedby="btnGroupAddon2">
        <div class="input-group-text" id="btnGroupAddon2"><i class="bi bi-search"></i></div>
      </div>
      <div class="btn-group mx-3" role="group" aria-label="First group">
        <button type="button" class="btn btn-primary">Cursos Agendados</button>
      </div>
    </div>

  </div> --}}
  {{-- pesquisa --}}

  {{-- cardCursos agendados --}}
  <div class="container justify-content-center mt-5">
    <div class="row gy-4 mx-auto">
      @foreach ($agendacursos as $agendacurso)
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3 d-flex justify-content-center">
          <div class="card ribbon-box border shadow-none mb-lg-0 card-interlab" style="width: 18rem; background-image: url('{{ $agendacurso->curso->thumb ?? ''
            ? asset('curso-thumb/' . $agendacurso->curso->thumb)
            : ($agendacurso->tipo_agendamento == 'ONLINE'
              ? asset('build/images/site/online-placeholder.jpg')
              : asset('build/images/site/evento-placeholder.jpg')) }}'); background-size: cover; background-repeat: no-repeat">
            @if( $agendacurso->inscricoes == 1 )
              <div class="ribbon ribbon-success round-shape">Inscrições abertas</div>
            @endif
            <div style="margin-top: 8rem">
              <div class="card-body text-white" style="background-color: #405D71;">
                <a href="{{ route('curso-agendado-show', $agendacurso->uid) }}" class="text-white bold">
                  <h5 class="card-title pb-2">{{ $agendacurso->curso->descricao }}</h5>
                </a>
      
                <a href="{{ route('curso-agendado-show', $agendacurso->uid) }}" class="text-start text-white bold">Visualizar <i
                  class="fa-solid fa-circle-chevron-right"></i></a>
              </div>
              <div class="card-footer py-2 border-0 text-white" style="background-color: #002C41">
                <i class="bi bi-calendar2-event"></i> &nbsp; 
                  @if($agendacurso->data_inicio) {{ \Carbon\Carbon::parse($agendacurso->data_inicio)->format('d/m/Y') }} @endif 
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endsection
