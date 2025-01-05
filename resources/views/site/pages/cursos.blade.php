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
  <div class="container my-5">
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

  </div>
  {{-- pesquisa --}}

  {{-- cardCursos agendados --}}
  <div class="container row justify-content-center">

    @foreach ($agendacursos as $agendacurso)
      <div class="col-3 mt-4" style="width: 18rem; height: 20rem;">

        <div class="SiteCards__bgimage text-white d-grid"
          style="background-image: url('{{ $agendacurso->curso->thumb ?? ''
            ? asset('curso-thumb/' . $agendacurso->curso->thumb)
            : ($agendacurso->tipo_agendamento == 'ONLINE'
              ? asset('build/images/site/online-placeholder.jpg')
              : asset('build/images/site/evento-placeholder.jpg')) }}');">
          <div class="SiteCards--efeito  align-self-end d-grid align-self-end align-items-end p-3">
            <a href="{{ route('curso-agendado-show', $agendacurso->uid) }}"
              class=" align-self-center text-center h5 text-white SiteCards__descricao" style="height: 100%;">
              {{ $agendacurso->curso->descricao }}
            </a>
            <a href="{{ route('curso-agendado-show', $agendacurso->uid) }}" class="text-start text-white bold">
              <i class="fa fa-clock-o" aria-hidden="true"></i>
              {{ \Carbon\Carbon::parse($agendacurso->data_inicio)->format('d/m/Y') }}
            </a>
          </div>

        </div>
      </div>
    @endforeach
  </div>




  <!-- helper modal -->
  <div class="modal fade" id="cursosHelper" tabindex="-1" aria-labelledby="cursosHelperLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-right">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cursosHelperLabel">Estamos atualizando nosso sistema</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Estamos em processo de atualização de nosso sistema. <br>
            A agenda de cursos está sendo atualizado e em breve estará completamente disponível. <br><br>
            Caso o curso que você procura não esteja listado, solicite informações através do e-mail:
            <a href="mailto:contato@redemetrologica.com.br">contato@redemetrologica.com.br</a>
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="localStorage.setItem('cursosHelper', 'false')">Não mostrar novamente</button>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>
  
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('cursosHelper') !== 'false') { new bootstrap.Modal('#cursosHelper').show(); }
  });
  </script>

@endsection
